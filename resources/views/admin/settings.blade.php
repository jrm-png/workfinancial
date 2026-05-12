<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | System Control Panel</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
        
        /* Main Content Area */
        .content { margin-left: 230px; padding: 40px; box-sizing: border-box; }
        
        .header-section { margin-bottom: 30px; }
        
        /* Cards */
        .section-card { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
            box-sizing: border-box; height: 100%;
        }

        /* Form elements with cleaner UI */
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { 
            width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; 
            border-radius: 8px; font-size: 14px; box-sizing: border-box; 
            transition: all 0.2s; font-family: 'Inter', sans-serif; color: #334155;
            background-color: #ffffff;
        }
        .form-input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        /* Traditional native calendar inputs cleanup */
        input[type="date"].form-input {
            position: relative;
            cursor: pointer;
        }

        /* Layout Grid Matrix */
        .grid-top { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; margin-bottom: 30px; align-items: start; }
        .grid-dropdowns { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        /* Table */
        .override-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .override-table th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .override-table td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .override-table tr:hover { background-color: #f8fafc; }

        /* Buttons & Sliders */
        .btn-primary { background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; font-family: 'Inter', sans-serif; font-size: 14px; }
        .btn-primary:hover { background: #1d4ed8; }
        
        .btn-status { border: none; cursor: pointer; padding: 6px 16px; border-radius: 20px; font-size: 11px; font-weight: 700; transition: 0.2s; letter-spacing: 0.5px; }
        .btn-status.active { background: #dcfce7; color: #15803d; }
        .btn-status:not(.active) { background: #fee2e2; color: #b91c1c; }

        .btn-danger-icon { background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px; border-radius: 4px; transition: 0.2s; }
        .btn-danger-icon:hover { color: #b91c1c; background: #fee2e2; }

        /* Switch Toggle */
        .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: #2563eb; }
        input:checked + .slider:before { transform: translateX(22px); }

        /* Notifications */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-size: 14px; }

        /* Dynamic category scrolls */
        .dropdown-list-box { max-height: 180px; overflow-y: auto; padding-right: 5px; border: 1px solid #f1f5f9; border-radius: 8px; margin-top: 8px; }
        .dropdown-item-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-bottom: 1px solid #f8fafc; font-size: 13px; color: #334155; }
        .dropdown-item-row:last-child { border-bottom: none; }
    </style>
</head>
<body>

@include('layouts.app')

<div class="content">
    <div class="header-section">
        <h1 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">System Control Panel</h1>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-top">
        
        <div class="section-card">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; color: #1e293b;">
                <i class="fas fa-calendar-alt" style="color: #2563eb;"></i> Global Window Settings
            </h2>
            
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label class="form-label">Submission Start Date</label>
                    <input type="date" name="submission_start" class="form-input" value="{{ $settings->submission_start }}">
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label class="form-label">Submission End Date</label>
                    <input type="date" name="submission_end" class="form-input" value="{{ $settings->submission_end }}">
                </div>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; gap: 10px;">
                    <div>
                        <label class="form-label" style="margin:0; text-transform:none; font-size:14px;">Data Viewing Access</label>
                        <small style="color:#64748b; display:block; margin-top:2px;">Allow regular users to see the Workplan List database layout profile</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="is_viewing_open" {{ $settings->is_viewing_open ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-save"></i> Save Global Settings
                </button>
            </form>
        </div>

        <div class="section-card">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px; color: #0f766e;">
                <i class="fas fa-list-ul" style="color: #0f766e;"></i> Global Dropdown Options Manager
            </h2>
            
            <form method="POST" action="{{ route('admin.dropdowns.store') }}" style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                @csrf
                <div style="display: grid; grid-template-columns: 1.2fr 1.5fr auto; gap: 12px; align-items: flex-end;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Category Type</label>
                        <select name="type" class="form-input" required style="padding: 9px 12px; font-size: 13px;">
                            <option value="strategic_perspective">Strategic Perspective</option>
                            <option value="expense_class">Expense Class</option>
                            <option value="funds">Source of Funds</option>
                            <option value="programs">Major Programs</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Option Input Value Text</label>
                        <input type="text" name="value" class="form-input" placeholder="Enter option choice..." required style="padding: 9px 12px; font-size: 13px;">
                    </div>
                    <div>
                        <button type="submit" class="btn-primary" style="padding: 10px 16px; font-size: 13px; height: 39px;">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
            </form>

            <div class="grid-dropdowns">
                @php
                    $categories = [
                        'strategic_perspective' => 'Strategic Perspectives',
                        'expense_class' => 'Expense Classes',
                        'funds' => 'Source of Funds',
                        'programs' => 'Major Programs'
                    ];
                @endphp

                @foreach($categories as $key => $title)
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; display: block; border-bottom: 2px solid #f1f5f9; padding-bottom: 4px;">
                            {{ $title }}
                        </span>
                        
                        <div class="dropdown-list-box">
                            @if(isset($dropdowns[$key]) && count($dropdowns[$key]) > 0)
                                @foreach($dropdowns[$key] as $item)
                                    <div class="dropdown-item-row">
                                        <span style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 85%;" title="{{ $item->value }}">{{ $item->value }}</span>
                                        <form method="POST" action="{{ route('admin.dropdowns.delete', $item->id) }}" onsubmit="return confirm('Remove this option?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger-icon">
                                                <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @else
                                <div style="padding: 15px; text-align: center; color: #94a3b8; font-size: 12px; font-style: italic;">Empty</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="section-card" style="width: 100%;">
        <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 6px 0; display: flex; align-items: center; gap: 10px; color: #1e293b;">
            <i class="fas fa-user-shield" style="color: #64748b;"></i> Division Submission Overrides
        </h2>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 20px 0;">The following centers can still process submissions or view tables even if the main application window is locked/closed.</p>
        
        <div style="overflow-x: auto; width: 100%;">
            <table class="override-table">
                <thead>
                    <tr>
                        <th>Responsibility Center</th>
                        <th>Authorized Name Profile</th>
                        <th style="text-align: center; width: 180px;">Override Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td style="font-weight: 600; color: #1e293b;">{{ $user->responsibility_center }}</td>
                        <td style="color: #475569;">{{ $user->name }}</td>
                        <td style="text-align: center;">
                            <button onclick="toggleOverride({{ $user->id }})" 
                                    class="btn-status {{ $user->can_override_submission ? 'active' : '' }}">
                                {{ $user->can_override_submission ? 'ENABLED' : 'DISABLED' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleOverride(userId) {
    }
</script>

</body>
</html>