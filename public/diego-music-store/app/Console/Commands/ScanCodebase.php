<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScanCodebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:scan {--type=all : Types to scan: helpers, actions, filament, all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan codebase components (Helpers, Actions, Filament Resources) to understand structure and usage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info("==================================================");
        $this->info("      DIEGO MUSIC STORE ERP - CODEBASE SCAN       ");
        $this->info("==================================================");

        if ($type === 'all' || $type === 'helpers') {
            $this->scanHelpers();
        }

        if ($type === 'all' || $type === 'actions') {
            $this->scanActions();
        }

        if ($type === 'all' || $type === 'filament') {
            $this->scanFilamentResources();
        }

        $this->info("Scan complete!");
        return self::SUCCESS;
    }

    /**
     * Scan and display Helper classes.
     */
    private function scanHelpers()
    {
        $this->comment("\n--- Scanning Helpers (App\\Helpers\\*) ---");
        $helperPath = app_path('Helpers');

        if (!File::isDirectory($helperPath)) {
            $this->warn("Helpers directory not found at: {$helperPath}");
            return;
        }

        $files = File::allFiles($helperPath);
        if (empty($files)) {
            $this->line("No helper classes found.");
            return;
        }

        foreach ($files as $file) {
            $className = 'App\\Helpers\\' . str_replace('.php', '', $file->getFilename());
            
            if (!class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            $classDoc = $this->cleanDocComment($reflection->getDocComment());
            
            $this->line("");
            $this->info("🛠️  Helper: " . class_basename($className) . " (" . $classDoc . ")");
            $this->line("   Class: \\{$className}");

            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            if (empty($methods)) {
                $this->line("   (No public methods found)");
                continue;
            }

            $headers = ['Method Signature', 'Description'];
            $rows = [];

            foreach ($methods as $method) {
                // Ignore constructor or magic methods
                if (str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $signature = $this->getMethodSignature($method);
                $doc = $this->cleanDocComment($method->getDocComment());
                
                $rows[] = [$signature, $doc];
            }

            if (!empty($rows)) {
                $this->table($headers, $rows);
            }
        }
    }

    /**
     * Scan and display Action classes.
     */
    private function scanActions()
    {
        $this->comment("\n--- Scanning Action Pattern (App\\Actions\\*) ---");
        $actionPath = app_path('Actions');

        if (!File::isDirectory($actionPath)) {
            $this->warn("Actions directory not found at: {$actionPath}");
            return;
        }

        $files = File::allFiles($actionPath);
        if (empty($files)) {
            $this->line("No action classes found.");
            return;
        }

        // Group actions by feature module
        $modules = [];

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname(); // e.g. "Product/DuplicateProduct.php"
            $className = 'App\\Actions\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $parts = explode('/', $relativePath);
            $moduleName = count($parts) > 1 ? $parts[0] : 'General';
            
            $modules[$moduleName][] = $className;
        }

        foreach ($modules as $module => $classes) {
            $this->line("");
            $this->info("📦 Feature Module: {$module}");
            
            $headers = ['Action Class', 'Execute Signature', 'Description'];
            $rows = [];

            foreach ($classes as $className) {
                $reflection = new \ReflectionClass($className);
                $classDoc = $this->cleanDocComment($reflection->getDocComment());

                // Find execute method
                if ($reflection->hasMethod('execute')) {
                    $method = $reflection->getMethod('execute');
                    $signature = $this->getMethodSignature($method);
                    $methodDoc = $this->cleanDocComment($method->getDocComment());
                    $desc = ($classDoc !== '-' ? $classDoc : '') . ($methodDoc !== '-' ? ($classDoc !== '-' ? ' | ' : '') . $methodDoc : '');
                    if (empty($desc)) {
                        $desc = '-';
                    }
                } else {
                    $signature = 'N/A (Missing execute method)';
                    $desc = $classDoc;
                }

                $rows[] = [class_basename($className), $signature, $desc];
            }

            $this->table($headers, $rows);
        }
    }

    /**
     * Scan and display Filament Resource classes.
     */
    private function scanFilamentResources()
    {
        $this->comment("\n--- Scanning Filament Resources (App\\Filament\\Resources\\*) ---");
        $resourcePath = app_path('Filament/Resources');

        if (!File::isDirectory($resourcePath)) {
            $this->warn("Filament Resources directory not found at: {$resourcePath}");
            return;
        }

        // Let's recursively find Resource.php files or find them in folders
        $files = File::allFiles($resourcePath);
        $resourceClasses = [];

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            // We want files that end with Resource.php
            if (!str_ends_with($file->getFilename(), 'Resource.php')) {
                continue;
            }

            $className = 'App\\Filament\\Resources\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);
            
            if (class_exists($className) && is_subclass_of($className, 'Filament\\Resources\\Resource')) {
                $resourceClasses[] = $className;
            }
        }

        if (empty($resourceClasses)) {
            $this->line("No Filament Resource classes found.");
            return;
        }

        $headers = ['Resource Name', 'Model', 'Nav Group', 'Label', 'Pages / Sub-pages'];
        $rows = [];

        foreach ($resourceClasses as $className) {
            $reflection = new \ReflectionClass($className);

            // Model
            try {
                $model = $className::getModel();
                $modelBasename = class_basename($model);
            } catch (\Throwable $e) {
                $modelProp = $reflection->getProperty('model');
                $modelProp->setAccessible(true);
                $modelVal = $modelProp->getValue() ?? '-';
                $modelBasename = class_basename($modelVal);
            }

            // Nav Group
            try {
                $navGroup = $className::getNavigationGroup() ?? '-';
            } catch (\Throwable $e) {
                $navGroup = '-';
            }

            // Label
            try {
                $label = $className::getLabel() ?? '-';
            } catch (\Throwable $e) {
                $label = '-';
            }

            // Pages
            try {
                $pages = $className::getPages();
                $pageList = [];
                foreach ($pages as $key => $route) {
                    $pageClass = $route->getPage();
                    $pageList[] = "{$key}: " . class_basename($pageClass);
                }
                $pagesStr = implode("\n", $pageList);
            } catch (\Throwable $e) {
                $pagesStr = 'Error loading pages';
            }

            $rows[] = [
                class_basename($className),
                $modelBasename,
                $navGroup,
                $label,
                $pagesStr
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Parse method signature nicely.
     */
    private function getMethodSignature(\ReflectionMethod $method): string
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $typeStr = '';
            
            if ($type instanceof \ReflectionNamedType) {
                $typeStr = $type->getName() . ' ';
            } elseif ($type instanceof \ReflectionUnionType) {
                $unionTypes = [];
                foreach ($type->getTypes() as $t) {
                    $unionTypes[] = $t->getName();
                }
                $typeStr = implode('|', $unionTypes) . ' ';
            }
            
            $paramStr = $typeStr . '$' . $param->getName();
            
            if ($param->isOptional()) {
                try {
                    $default = $param->getDefaultValue();
                    if (is_array($default)) {
                        $paramStr .= ' = []';
                    } elseif (is_null($default)) {
                        $paramStr .= ' = null';
                    } elseif (is_string($default)) {
                        $paramStr .= " = '" . $default . "'";
                    } elseif (is_bool($default)) {
                        $paramStr .= ' = ' . ($default ? 'true' : 'false');
                    } else {
                        $paramStr .= ' = ' . $default;
                    }
                } catch (\Throwable $e) {
                    $paramStr .= ' = <unknown>';
                }
            }
            $params[] = $paramStr;
        }

        $returnType = $method->getReturnType();
        $returnTypeStr = '';
        if ($returnType instanceof \ReflectionNamedType) {
            $returnTypeStr = ': ' . $returnType->getName();
        } elseif ($returnType instanceof \ReflectionUnionType) {
            $unionTypes = [];
            foreach ($returnType->getTypes() as $t) {
                $unionTypes[] = $t->getName();
            }
            $returnTypeStr = ': ' . implode('|', $unionTypes);
        }

        $prefix = $method->isStatic() ? 'static ' : '';

        return $prefix . $method->getName() . '(' . implode(', ', $params) . ')' . $returnTypeStr;
    }

    /**
     * Clean and strip docstring comments.
     */
    private function cleanDocComment(?string $docComment): string
    {
        if (!$docComment) {
            return '-';
        }

        $lines = explode("\n", $docComment);
        $cleanedLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '/**' || $line === '*/' || $line === '*') {
                continue;
            }
            
            if (str_starts_with($line, '/**')) {
                $line = substr($line, 3);
            }
            if (str_ends_with($line, '*/')) {
                $line = substr($line, 0, -2);
            }
            if (str_starts_with($line, '*')) {
                $line = ltrim(substr($line, 1));
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Ignore phpdoc tags in the brief description
            if (str_starts_with($line, '@')) {
                continue;
            }

            $cleanedLines[] = $line;
        }

        return implode(' ', $cleanedLines) ?: '-';
    }
}
