<?php

class Router
{
    public function dispatch(string $url): void
    {
        $url = trim($url, '/');
        $segments = $url === '' ? [] : explode('/', $url);

        $controllerSegment = $segments[0] ?? 'home';
        $method            = $segments[1] ?? 'index';
        $params            = array_slice($segments, 2);

        // sanitize method (only allow letters/numbers/underscore)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $method)) {
            $this->renderError(400, 'Bad Request', 'Invalid method name.');
            return;
        }

        $controllerName = $this->controllerClass($controllerSegment);
        $controllerFile = "app/controllers/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            $this->renderError(
                404,
                'Not Found',
                'The page you requested could not be found.',
                "Missing controller file: {$controllerFile}"
            );
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->renderError(
                500,
                'Server Error',
                'A server configuration error occurred.',
                "Missing controller class: {$controllerName}"
            );
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            $this->renderError(
                404,
                'Not Found',
                'The page you requested could not be found.',
                "Missing method: {$controllerName}::{$method}"
            );
            return;
        }

        try {
            call_user_func_array([$controller, $method], $params);
        } catch (Throwable $e) {
            $this->renderError(
                500,
                'Server Error',
                'Something went wrong while processing your request.',
                $e->getMessage()
            );
        }
    }

    private function renderError(int $code, string $title, string $message, ?string $details = null): void
    {
        http_response_code($code);

        $viewFile = "app/views/errors/{$code}.php";

        // Auto-detect base folder (e.g. /test)
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        $homeUrl = ($base === '' || $base === '/') ? '/' : $base . '/';

        if (!file_exists($viewFile)) {
            echo "{$code} {$title} - {$message}";
            if ($details) echo "<pre>" . htmlspecialchars($details) . "</pre>";
            return;
        }

        // Variables for the view
        $code = $code;
        $title = $title;
        $message = $message;
        $details = $details;
        $homeUrl = $homeUrl;

        require $viewFile;
    }

    /**
     * Convert URL segment to Controller class name.
     */
    private function controllerClass(string $segment): string
    {
        $segment = trim($segment);

        // allow only letters/numbers/_/-
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $segment)) {
            return 'HomeController';
        }

        $segment = str_replace(['-', '_'], ' ', strtolower($segment));
        $segment = str_replace(' ', '', ucwords($segment));

        return $segment . 'Controller';
    }
}
