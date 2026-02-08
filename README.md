# 🚀 MyPHP Framework (Personal MVC Micro-Framework)

A lightweight, educational, and extensible **PHP MVC micro-framework** built from scratch.

This framework is designed to be:
- Simple (no magic, no Composer required)
- Educational (every part is understandable)
- Flexible (easy to extend into your own framework)
- Professional (routing, controllers, models, views, GUI errors)

---

## 📦 Requirements

- PHP 8.0+
- Apache (XAMPP / WAMP / LAMP)
- MySQL / MariaDB
- mod_rewrite enabled

---

## 📁 Project Structure

```
/project-root
│
├── app/
│   ├── controllers/
│   ├── core/
│   ├── models/
│   └── views/
│       ├── errors/
│       └── home/
│
├── .env
├── .htaccess
├── index.php
└── README.md
```

---

## ⚙️ Installation

1. Copy the framework into your web root  
   Example: `htdocs/test`
2. Enable `mod_rewrite` in Apache
3. Create a `.env` file in the root
4. Visit `http://localhost/test`

---

## 🌐 Entry Point

All requests are handled by `index.php` using a front controller pattern.

---

## 🔁 Routing

URL format:

```
/controller/method/param1/param2
```

Examples:

| URL | Controller | Method |
|----|-----------|--------|
| `/` | HomeController | index |
| `/user/show/5` | UserController | show |

---

## 🎮 Controllers

Controllers handle application logic.

```php
class HomeController extends Controller
{
    public function index()
    {
        $this->view('home/index', [
            'name' => 'Mark'
        ]);
    }
}
```

Available helpers:
- `model()`
- `view()`
- `redirect()`
- `json()`
- `base_url()`
- `input()`
- `dd()`

---

## 🧱 Models

Models handle database logic only.

```php
class User
{
    public function all()
    {
        return Query::table('users')->get();
    }
}
```

---

## 🗄️ Database & Environment

`.env` example:

```env
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_NAME=test_mvc
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

- Uses PDO
- Prepared statements only
- Auto-creates database if missing

---

## 🔍 Query Builder

```php
Query::table('users')->get();

Query::table('users')
    ->where('id', 1)
    ->first();

Query::table('users')
    ->insert(['name' => 'Mark']);
```

---

## 🖼️ Views

Views are plain PHP files.

```php
<h1>Hello <?= htmlspecialchars($name) ?></h1>
```

No layout system by default (intentional).

---

## ❌ Error Handling

GUI error pages included:
- 400 Bad Request
- 404 Not Found
- 500 Server Error

Errors live in:

```
app/views/errors/
```

Debug details are shown only when:

```env
APP_DEBUG=true
```

---

## 🔒 Security

- PDO prepared statements
- Sanitized routes
- Escaped output helpers
- No SQL injection risks

---

## 🧠 Philosophy

This framework avoids:
- Composer
- Heavy abstractions
- Hidden magic

It focuses on:
- Learning
- Control
- Readability
- Customization

---

## 🚧 Suggested Next Steps

- PSR-4 Autoloading
- Middleware
- Authentication
- CLI tools
- Unit testing

---

## 📄 License

MIT License — use freely.
