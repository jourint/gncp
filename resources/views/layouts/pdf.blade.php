<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { 
            font-family: "DejaVu Sans", sans-serif; 
            font-size: 11px; 
            color: #1e293b; 
            line-height: 1.2;
            margin: 0; padding: 0;
        }
        /* Глобальные фиксы для dompdf */
        .grid, .flex { display: block !important; width: 100% !important; }
        
        /* Карточки (Зарплата, Крой) в ряд */
        .grid > div, .card-grid > div {
            display: inline-block !important;
            vertical-align: top !important;
            width: 31% !important;
            margin: 1% !important;
            border: 1px solid #cbd5e1 !important;
        }

        /* Размеры в ряд */
        .flex.items-baseline, .flex.flex-wrap > div {
            display: inline-block !important;
            margin-right: 8px !important;
            margin-bottom: 4px !important;
        }

        table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px; }
        th, td { border-bottom: 1px solid #e2e8f0 !important; padding: 6px 4px !important; text-align: left; }
        
        .font-black, .font-bold { font-weight: bold !important; }
        .italic { font-style: italic !important; }
        .uppercase { text-transform: uppercase !important; }
        .text-xl { font-size: 16px !important; }
        .border-b-2 { border-bottom: 2px solid #000 !important; }
        .page-break-inside-avoid { page-break-inside: avoid !important; display: block !important; }
        .ml-auto { float: right !important; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>