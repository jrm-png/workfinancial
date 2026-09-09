<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Settings - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
         .sidebar { width: 190px; height: 100vh; background: #1e293b; color: white; position: fixed; padding: 20px; box-shadow: 4px 0 15px rgba(0,0,0,0.1); z-index: 100; }
        .nav-link { color: #94a3b8; font-size: 14px; padding: 10px 12px; display: flex; align-items: center; text-decoration: none; border-radius: 8px; transition: all 0.2s ease; margin-bottom: 5px; }
        .nav-link i { width: 22px; margin-right: 10px; font-size: 15px; }
        .nav-link:hover { background: #334155; color: #38bdf8; transform: translateX(3px); }
        .content { margin-left: 220px; padding: 40px; width: calc(100% - 220px); box-sizing: border-box; }
        
        .admin-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .header-title { border-left: 5px solid #10b981; padding-left: 15px; font-size: 24px; font-weight: 800; margin-bottom: 25px; }
        
        /* Search and Form Grid */
        .control-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; align-items: end; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-label { font-weight: 600; font-size: 13px; color: #475569; text-transform: uppercase; }
        .form-input { padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        
        .btn-add { background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-add:hover { background: #059669; }
        .btn-delete { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; transition: 0.2s; }
        .btn-delete:hover { color: #b91c1c; }

        /* Tabs Styling */
        .tabs-container { display: flex; gap: 10px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; padding-bottom: 5px; overflow-x: auto; }
        .tab-btn { padding: 10px 20px; border: none; background: none; cursor: pointer; font-weight: 600; color: #64748b; border-radius: 6px; transition: 0.2s; white-space: nowrap; }
        .tab-btn.active { background: #1e293b; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Items List Table/Rows */
        .options-list { max-height: 450px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        .option-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-bottom: 1px solid #e2e8f0; background: white; }
        .option-item:last-child { border-bottom: none; }
        .option-item.hidden { display: none !important; }
        
        /* Alerts */
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 14px; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>
        @include('layouts.app')
</body>
</html>