<?php
// Expected variables:
// $code (int|string), $title (string), $message (string), $details (?string), $homeUrl (string), $accent (string), $icon (string)

$code    = $code ?? 500;
$title   = $title ?? 'Error';
$message = $message ?? 'Something went wrong.';
$details = $details ?? null;
$homeUrl = $homeUrl ?? '/';
$accent  = $accent ?? '#60a5fa'; // default blue

$isDebug = !empty($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars((string)$code) ?> — <?= htmlspecialchars($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg1: #070B14;
            --bg2: #0B1530;
            --card: rgba(255, 255, 255, 0.06);
            --border: rgba(255, 255, 255, 0.10);
            --text: #E7EAF2;
            --muted: rgba(231, 234, 242, 0.70);
            --muted2: rgba(231, 234, 242, 0.55);
            --accent: <?= htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') ?>;
            --shadow: 0 20px 60px rgba(0, 0, 0, 0.55);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background:
                radial-gradient(1200px 600px at 20% 10%, rgba(96, 165, 250, 0.18), transparent 50%),
                radial-gradient(900px 500px at 80% 30%, rgba(251, 191, 36, 0.10), transparent 50%),
                linear-gradient(160deg, var(--bg1), var(--bg2));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
        }

        .wrap {
            width: 100%;
            max-width: 880px;
        }

        .card {
            position: relative;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            backdrop-filter: blur(10px);
            transform: translateY(10px);
            opacity: 0;
            animation: enter 520ms ease-out forwards;
        }

        .topbar {
            height: 4px;
            background: linear-gradient(90deg, var(--accent), rgba(255, 255, 255, 0.15));
        }

        .content {
            padding: 26px 26px 22px;
            display: grid;
            grid-template-columns: 56px 1fr;
            gap: 16px;
            align-items: start;
        }

        .icon {
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .code {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: var(--text);
            letter-spacing: .08em;
            font-weight: 650;
            font-size: 11px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 750;
            line-height: 1.15;
        }

        p {
            margin: 0 0 16px;
            color: var(--muted);
            line-height: 1.55;
            font-size: 15px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .btn {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 650;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 120ms ease, background 120ms ease, border-color 120ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.18);
        }

        .btn.primary {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.95), rgba(59, 130, 246, 0.95));
            border-color: rgba(59, 130, 246, 0.35);
        }

        .btn.primary:hover {
            background: linear-gradient(90deg, rgba(29, 78, 216, 0.98), rgba(37, 99, 235, 0.98));
        }

        .meta {
            padding: 0 26px 22px;
            color: var(--muted2);
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        details {
            margin: 0 26px 26px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        summary {
            cursor: pointer;
            padding: 12px 14px;
            font-weight: 650;
            color: var(--text);
            list-style: none;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        pre {
            margin: 0;
            padding: 14px;
            overflow: auto;
            color: rgba(231, 234, 242, 0.88);
            font-size: 12.5px;
            line-height: 1.45;
            border-top: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(0, 0, 0, 0.18);
            white-space: pre-wrap;
        }

        @keyframes enter {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 640px) {
            .content {
                grid-template-columns: 1fr;
            }

            .icon {
                width: 52px;
                height: 52px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card" role="alert" aria-live="polite">
            <div class="topbar"></div>

            <div class="content">
                <div class="icon" aria-hidden="true">
                    <?= $icon ?? '' ?>
                </div>

                <div>
                    <div class="code">
                        <span class="pill"><?= htmlspecialchars((string)$code) ?></span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </div>

                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p><?= htmlspecialchars($message) ?></p>

                    <div class="actions">
                        <a class="btn primary" href="<?= htmlspecialchars($homeUrl) ?>">← Go Home</a>
                        <a class="btn" href="javascript:history.back()">↩ Back</a>
                    </div>
                </div>
            </div>

            <div class="meta">
                <span>Request: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '-') ?></span>
                <span>Time: <?= htmlspecialchars(date('Y-m-d H:i:s')) ?></span>
            </div>

            <?php if ($isDebug && !empty($details)): ?>
                <details>
                    <summary>Technical details (debug)</summary>
                    <pre><?= htmlspecialchars($details) ?></pre>
                </details>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>