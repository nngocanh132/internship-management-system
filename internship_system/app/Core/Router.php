<?php
class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $controller, string $action): void
    {
        $this->routes[] = compact('method','pattern','controller','action');
    }

    public function dispatch(string $uri, string $method): void
    {
        // Strip query string
        $uri = strtok($uri, '?');

        // Remove base prefix
        $base = '/internship-management-system/internship_system';
        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        if (empty($uri)) $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && strtoupper($method) !== strtoupper($route['method'])) continue;

            // Convert /path/to/file.php pattern to regex
            $pattern = '#^' . preg_quote($route['pattern'], '#') . '$#';
            if (!preg_match($pattern, $uri)) continue;

            $ctrlFile = __DIR__ . '/../Controllers/' . $route['controller'] . '.php';
            if (!file_exists($ctrlFile)) {
                http_response_code(500);
                die("Controller not found: {$route['controller']}");
            }
            require_once $ctrlFile;
            $ctrl = new $route['controller']($GLOBALS['conn']);
            $ctrl->{$route['action']}();
            return;
        }

        // 404
        http_response_code(404);
        require_once __DIR__ . '/../Views/errors/404.php';
    }
}
