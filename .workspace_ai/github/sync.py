import os
import re
import json
import urllib.request
import urllib.error

# Config
REPO = os.environ.get("GITHUB_REPOSITORY")
TOKEN = os.environ.get("GITHUB_TOKEN")
API_URL = "https://api.github.com"

if not REPO or not TOKEN:
    print("Error: GITHUB_REPOSITORY and GITHUB_TOKEN environment variables must be set.")
    exit(1)

headers = {
    "Authorization": f"token {TOKEN}",
    "Accept": "application/vnd.github.v3+json",
    "User-Agent": "Workspace-AI-Sync-Script"
}

def make_request(url, method="GET", data=None):
    req = urllib.request.Request(url, headers=headers, method=method)
    if data:
        req.data = json.dumps(data).encode("utf-8")
        req.add_header("Content-Type", "application/json")
    try:
        with urllib.request.urlopen(req) as response:
            return json.loads(response.read().decode("utf-8")), response.status
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8")
        print(f"HTTP Error {e.code}: {e.reason}\nBody: {body}")
        return None, e.code

# --- 1. Get Existing Milestones and Issues ---
print("Fetching existing Milestones from GitHub...")
milestones, status = make_request(f"{API_URL}/repos/{REPO}/milestones?state=all")
milestones = milestones or []
milestone_map = {m["title"]: m["number"] for m in milestones}

print("Fetching existing Issues from GitHub...")
issues = []
page = 1
while True:
    page_issues, status = make_request(f"{API_URL}/repos/{REPO}/issues?state=all&per_page=100&page={page}")
    if not page_issues:
        break
    issues.extend(page_issues)
    if len(page_issues) < 100:
        break
    page += 1

# Issue title prefix mapping (e.g. TASK-001 -> issue)
issue_map = {}
for iss in issues:
    # Milestones are issues too in API responses, but they don't have pull_request key and do have pull_request if PR
    if "pull_request" in iss:
        continue
    match = re.match(r"^(TASK-\d+)", iss["title"])
    if match:
        issue_map[match.group(1)] = {
            "number": iss["number"],
            "title": iss["title"],
            "state": iss["state"],
            "body": iss["body"]
        }

# --- 2. Parse and Sync Epics (Milestones) ---
epics_dir = ".workspace_ai/workspace/epics"
if os.path.exists(epics_dir):
    for filename in sorted(os.listdir(epics_dir)):
        if not filename.endswith(".md"):
            continue
        path = os.path.join(epics_dir, filename)
        with open(path, "r", encoding="utf-8") as f:
            content = f.read()

        # Parse Epic Name
        # Format: # Epic: EPIC-001 - Master Data Setup & Basic Config
        title_match = re.search(r"^# Epic:\s*(EPIC-\d+\s*-\s*.*)$", content, re.MULTILINE)
        if not title_match:
            print(f"Skipping Epic {filename}: Title pattern not found.")
            continue
        epic_title = title_match.group(1).strip()

        # Parse status
        # Progress/status checking
        status_match = re.search(r"-\s*\*\*Status\*\*:\s*(\w+)", content)
        state = "open"
        if status_match and status_match.group(1).lower() in ["done", "completed", "closed"]:
            state = "closed"

        description = f"Epic parsed from local {filename}"

        if epic_title in milestone_map:
            # Update Milestone
            m_number = milestone_map[epic_title]
            print(f"Updating Milestone: {epic_title} (Number: {m_number})")
            make_request(
                f"{API_URL}/repos/{REPO}/milestones/{m_number}",
                method="PATCH",
                data={"description": description, "state": state}
            )
        else:
            # Create Milestone
            print(f"Creating Milestone: {epic_title}")
            res, code = make_request(
                f"{API_URL}/repos/{REPO}/milestones",
                method="POST",
                data={"title": epic_title, "description": description, "state": state}
            )
            if res:
                milestone_map[epic_title] = res["number"]

# --- 3. Parse and Sync Tasks (Issues) ---
tasks_dir = ".workspace_ai/execution/tasks"
if os.path.exists(tasks_dir):
    for task_folder in sorted(os.listdir(tasks_dir)):
        task_path = os.path.join(tasks_dir, task_folder)
        if not os.path.isdir(task_path):
            continue
        
        task_file = os.path.join(task_path, "task.md")
        if not os.path.exists(task_file):
            continue
        
        with open(task_file, "r", encoding="utf-8") as f:
            content = f.read()

        # Parse Task Title
        # Format: # Task: TASK-001 - Design Database Schema & Tenant isolation
        title_match = re.search(r"^# Task:\s*(TASK-\d+\s*-\s*.*)$", content, re.MULTILINE)
        if not title_match:
            print(f"Skipping Task in {task_folder}: Title pattern not found.")
            continue
        task_title = title_match.group(1).strip()
        task_id = task_folder

        # Parse Status
        status_match = re.search(r"-\s*\*\*Status\*\*:\s*([^\n\r]+)", content)
        local_status = status_match.group(1).strip() if status_match else "Backlog"

        # Determine state
        state = "closed" if local_status.lower() in ["done", "completed", "merged/completed", "merged", "closed"] else "open"
        
        # Parse Linked Epic ID
        epic_match = re.search(r"-\s*\*\*Epic\*\*:\s*(EPIC-\d+)", content)
        linked_epic_id = epic_match.group(1).strip() if epic_match else None
        
        # Parse Linked Feature ID
        feature_match = re.search(r"-\s*\*\*Feature\*\*:\s*(FEATURE-\d+)", content)
        linked_feature_id = feature_match.group(1).strip() if feature_match else None

        feature_details = ""
        if linked_feature_id:
            feature_file_path = f".workspace_ai/workspace/features/{linked_feature_id}.md"
            if os.path.exists(feature_file_path):
                try:
                    with open(feature_file_path, "r", encoding="utf-8") as ff:
                        feature_content = ff.read()
                    feature_details = f"\n\n<details>\n<summary><b>🔍 View Linked Feature Specs ({linked_feature_id})</b></summary>\n\n{feature_content}\n\n</details>"
                except Exception as e:
                    print(f"Error reading feature file {feature_file_path}: {e}")

        # Parse Subtasks Checklist
        subtasks_content = ""
        subtasks_file = os.path.join(task_path, "subtasks.md")
        if os.path.exists(subtasks_file):
            try:
                with open(subtasks_file, "r", encoding="utf-8") as sf:
                    subtasks_content = sf.read()
            except Exception as e:
                print(f"Error reading subtasks file {subtasks_file}: {e}")

        # Find corresponding Milestone Number
        milestone_number = None
        if linked_epic_id:
            for title, num in milestone_map.items():
                if title.startswith(linked_epic_id):
                    milestone_number = num
                    break

        body = f"### Specification & Details\nLocal Task Source: `.workspace_ai/execution/tasks/{task_id}/task.md`\n\n"
        body += "### Status label\n" + f"`status/{local_status.lower()}`\n\n"
        if subtasks_content:
            body += f"### Subtasks Checklist\n{subtasks_content}\n\n"
        if feature_details:
            body += feature_details

        labels = [f"status/{local_status.lower()}"]

        if task_id in issue_map:
            # Update Issue
            issue_info = issue_map[task_id]
            print(f"Updating Issue: {task_title} (Issue #{issue_info['number']})")
            make_request(
                f"{API_URL}/repos/{REPO}/issues/{issue_info['number']}",
                method="PATCH",
                data={
                    "title": task_title,
                    "body": body,
                    "state": state,
                    "milestone": milestone_number,
                    "labels": labels
                }
            )
        else:
            # Create Issue
            print(f"Creating Issue: {task_title}")
            make_request(
                f"{API_URL}/repos/{REPO}/issues",
                method="POST",
                data={
                    "title": task_title,
                    "body": body,
                    "milestone": milestone_number,
                    "labels": labels
                }
            )

print("=== Workspace AI Sync Completed Successfully ===")
