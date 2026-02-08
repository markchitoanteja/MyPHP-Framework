<?php

class Controller
{
    /* =========================
       MODELS
       ========================= */

    protected function model(string $model)
    {
        require_once "app/models/{$model}.php";
        return new $model();
    }

    /* =========================
       VIEWS
       ========================= */

    /**
     * Render a view
     *
     * @param string $view e.g. "home/index"
     * @param array $data variables passed to the view
     */
    protected function view(string $view, array $data = []): void
    {
        $viewFile = "app/views/{$view}.php";

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$viewFile}";
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    /* =========================
       URL HELPERS
       ========================= */

    /**
     * Get application base URL.
     *
     * Example: http://localhost/test
     */
    protected function base_url(string $path = ''): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // adjust if app lives in a subfolder
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        return rtrim("{$scheme}://{$host}{$scriptDir}", '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate a site URL relative to base.
     *
     * Example: site_url('home/users')
     */
    protected function site_url(string $path = ''): string
    {
        return $this->base_url($path);
    }

    /* =========================
       REQUEST HELPERS
       ========================= */

    /**
     * Get GET or POST input safely.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Check if request method matches.
     */
    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function isGet(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
    }

    /* =========================
       RESPONSE HELPERS
       ========================= */

    /**
     * HTML-escape helper
     */
    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirect to a path or full URL.
     */
    protected function redirect(string $path): void
    {
        if (!str_starts_with($path, 'http')) {
            $path = $this->base_url($path);
        }

        header("Location: {$path}");
        exit;
    }

    /**
     * Return JSON response.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /* =========================
       SESSION / FLASH
       ========================= */

    /**
     * Set flash message (available for next request).
     */
    protected function flash(string $key, string $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get and clear flash message.
     */
    protected function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /* =========================
       DEBUG
       ========================= */

    /**
     * Dump and die (dev helper).
     */
    protected function dd(mixed $value): void
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        exit;
    }
}
