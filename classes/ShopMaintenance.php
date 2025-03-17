<?php
/**
 * Copyright (C) 2017-2025 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2017-2025 thirty bees
 * @license   Open Software License (OSL 3.0)
 */

/**
 * Class ShopMaintenance
 *
 * This class implements tasks for maintaining the shop installation, to be
 * run on a regular schedule. It gets called by an asynchronous Ajax request
 * in DashboardController.
 */
class ShopMaintenanceCore
{
    /**
     * Run tasks as needed. Should take care of running tasks not more often
     * than needed and that one run takes not longer than a few seconds.
     *
     * This method gets triggered by the 'getNotifications' Ajax request, so
     * every two minutes while somebody has back office open.
     *
     * @throws PrestaShopException
     */
    public static function run()
    {
        $now = time();
        $lastRun = Configuration::getGlobalValue('SHOP_MAINTENANCE_LAST_RUN');
        if ($now - $lastRun > 86400) {
            // Run daily tasks.
            static::adjustThemeHeaders();
            static::optinShop();
            static::cleanAdminControllerMessages();
            static::cleanOldLogFiles();
            static::cleanOldThemeCacheFiles();
            static::autoDbBackup();
            static::deleteOldDbBackupFiles();

            Configuration::updateGlobalValue('SHOP_MAINTENANCE_LAST_RUN', $now);
        }
    }

    /**
     * Correct the "generator" meta tag in templates. Technology detection
     * sites like builtwith.com don't recognize thirty bees technology if the
     * theme template inserts a meta tag "generator" for PrestaShop.
     *
     * @return void
     */
    public static function adjustThemeHeaders()
    {
        foreach (scandir(_PS_ALL_THEMES_DIR_) as $themeDir) {
            if ( ! is_dir(_PS_ALL_THEMES_DIR_.$themeDir)
                || in_array($themeDir, ['.', '..'])) {
                continue;
            }

            $headerPath = _PS_ALL_THEMES_DIR_.$themeDir.'/header.tpl';
            if (is_writable($headerPath)) {
                $header = file_get_contents($headerPath);
                $newHeader = preg_replace('/<\s*meta\s*name\s*=\s*["\']generator["\']\s*content\s*=\s*["\'].*["\']\s*>/i',
                    '<meta name="generator" content="thirty bees">', $header);
                if ($newHeader !== $header) {
                    file_put_contents($headerPath, $newHeader);
                    Tools::clearSmartyCache();
                }
            }
        }
    }

    /**
     * Handle shop optin.
     *
     * @throws PrestaShopException
     */
    public static function optinShop()
    {
        $name = Configuration::STORE_REGISTERED;
        if ( ! Configuration::get($name)) {
            $employees = Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_);
            // Usually there's only one employee when we run this code.
            foreach ($employees as $employee) {
                $employee = new Employee($employee['id_employee']);
                $employee->optin = true;
                if ($employee->update()) {
                    Configuration::updateValue($name, 1);
                }
            }
        }
    }

    /**
     * Delete lost AdminController messages.
     *
     * @return void
     */
    public static function cleanAdminControllerMessages()
    {
        $name = AdminController::MESSAGE_CACHE_PATH;
        $nameLength = strlen($name);
        foreach (scandir(_PS_CACHE_DIR_) as $candidate) {
            if (substr($candidate, 0, $nameLength) === $name) {
                $path = _PS_CACHE_DIR_.'/'.$candidate;
                if (time() - filemtime($path) > 3600) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * Delete all .log files in the /log/ directory older than 6 months.
     *
     * @return void
     * @throws PrestaShopException
     */
    public static function cleanOldLogFiles()
    {
        $now = time();
        $days = Configuration::getLogsRetentionPeriod();
        $oldlogdeleteperiod = $days * 86400;
        $logDir = _PS_ROOT_DIR_ . '/log/';

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($logDir));
        foreach ($iterator as $item) {
            $filePath = $item->getPathname();
            if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'log' && is_writable($filePath)) {
                if ($now - filemtime($filePath) > $oldlogdeleteperiod) {
                    unlink($filePath);
                }
            }
        }
    }

    /**
     * Delete all .js and .css files in /themes/../cache/ directories older than 30 days.
     *
     * @return void
     * @throws PrestaShopException
     */
    public static function cleanOldThemeCacheFiles()
    {
        $days = Configuration::getCCCAssetsRetentionPeriod();
        $themesDir = _PS_ROOT_DIR_ . '/themes/';
        $now = time();
        $themecachedeleteperiod = $days * 86400;

        foreach (scandir($themesDir) as $themeName) {
            $themeDir = $themesDir . $themeName;
            $cacheDir = $themeDir . '/cache/';
            if (!in_array($themeName, ['.', '..']) && is_dir($themeDir) && is_dir($cacheDir)) {
                foreach (scandir($cacheDir) as $file) {
                    $filePath = $cacheDir . $file;
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    if (is_file($filePath) && ($extension === 'js' || $extension === 'css')) {
                        if ($now - filemtime($filePath) > $themecachedeleteperiod) {
                            unlink($filePath);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Automatically create a database backup if the automatic backup feature is enabled.
     *
     * @return void
     * @throws PrestaShopException
     */
    public static function autoDbBackup()
    {
        if (Configuration::get('TB_DB_AUTO_BACKUP')) {
            $backup = new PrestaShopBackup();
            if ($backup->add()) {
                PrestaShopLogger::addLog('Automatic backup created: ' . basename($backup->id), 1, null, 'ShopMaintenance', null, true);
            }
        }
    }
    
    /**
     * Delete backup files older than the configured retention period.
     *
     * @return void
     * @throws PrestaShopException
     */
    public static function deleteOldDbBackupFiles()
    {
        $retentionDays = (int) Configuration::get('TB_DB_BACKUP_RETENTION_PERIOD');
        if ($retentionDays <= 0) {
            return;
        }

        $backupDir = realpath(_PS_ADMIN_DIR_.PrestaShopBackup::$backupDir);
        if ($backupDir === false) {
            PrestaShopLogger::addLog('Backup directory not found.', 3, null, 'ShopMaintenance', null, true);
            return;
        }
        $now = time();
        $files = glob($backupDir . DIRECTORY_SEPARATOR . '*');

        // Only process files that match the expected backup filename pattern:
        // e.g. 1618821234-abc123.sql, 1618821234-abc123.sql.gz or 1618821234-abc123.sql.bz2
        $pattern = '/^\d+\-[a-f0-9]+\.sql(\.gz|\.bz2)?$/i';

        foreach ($files as $file) {
            if (is_file($file) && preg_match($pattern, basename($file))) {
                $ageDays = ($now - filemtime($file)) / 86400;
                if ($ageDays > $retentionDays) {
                    if (unlink($file)) {
                        PrestaShopLogger::addLog('Deleted old backup file: ' . basename($file), 1, null, 'ShopMaintenance', null, true);
                    } else {
                        PrestaShopLogger::addLog('Error deleting backup file: ' . basename($file), 3, null, 'ShopMaintenance', null, true);
                    }
                }
            }
        }
    }
}
