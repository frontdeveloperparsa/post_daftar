<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Security.php';

Auth::init();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle ?? SITE_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        
        /* اسکرول‌بار مینیمال */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: background 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }
        
        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
