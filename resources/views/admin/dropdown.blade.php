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

    <div class="content">
    <div class="admin-card">
        <h1 class="header-title"><i class="fas fa-sliders-h"></i> Dropdown Settings Management</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="control-grid">
            <form action="{{ route('admin.dropdowns.store') }}" method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; width: 100%; align-items: end;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Dropdown Type</label>
                    <select name="type" id="form-type-select" class="form-input" required onchange="switchTab(this.value)">
                        @foreach($dropdownTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">New Option Value</label>
                    <input type="text" name="value" class="form-input" placeholder="Type new option here..." required autocomplete="off">
                </div>
                <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Add Item</button>
            </form>

            <div class="form-group">
                <label class="form-label" style="color: #2563eb;"><i class="fas fa-search"></i> Filter / Search Items</label>
                <input type="text" id="searchBar" class="form-input" placeholder="Search item to update/remove..." oninput="filterDropdownItems()">
            </div>
        </div>

        <div class="tabs-container">
            @foreach($dropdownTypes as $key => $label)
                <button type="button" class="tab-btn {{ $loop->first ? 'active' : '' }}" id="tab-btn-{{ $key }}" onclick="switchTab('{{ $key }}')">
                    {{ $label }} 
                    <span style="font-size: 11px; opacity: 0.7;">({{ isset($options[$key]) ? count($options[$key]) : 0 }})</span>
                </button>
            @endforeach
        </div>

        @foreach($dropdownTypes as $key => $label)
            <div class="tab-content {{ $loop->first ? 'active' : '' }}" id="tab-content-{{ $key }}">
                <div class="options-list">
                    @if(isset($options[$key]) && count($options[$key]) > 0)
                        @foreach($options[$key] as $item)
                            <div class="option-item" data-search-value="{{ strtolower($item->value) }}">
                                <span style="font-weight: 500; font-size: 15px;">{{ $item->value }}</span>
                                <form action="{{ route('admin.dropdowns.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Sigurado ka bang nais mong tanggalin ito?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i> Remove</button>
                                </form>
                            </div>
                        @endforeach
                    @else
                        <div style="padding: 30px; text-align: center; color: #94a3b8; font-style: italic; font-size: 14px;">
                            The list for {{ $label }} is empty.
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

    <script>
        // --- TAB SWITCHER LOGIC ---
        function switchTab(typeKey) {
            // Remove active status across tabs
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Activate target components
            document.getElementById(`tab-btn-${typeKey}`).classList.add('active');
            document.getElementById(`tab-content-${typeKey}`).classList.add('active');

            // I-sync din yung Form Select para kung ano yung tinitingnan mong tab, doon mag-aadd
            document.getElementById('form-type-select').value = typeKey;

            // Clear search field automatic pag nag-palit ng tab
            const searchBar = document.getElementById('searchBar');
            searchBar.value = '';
            filterDropdownItems();
        }

        // --- REAL-TIME JAVASCRIPT FILTER/SEARCH LOGIC ---
        function filterDropdownItems() {
            const query = document.getElementById('searchBar').value.toLowerCase().trim();
            // Hanapin lang natin ang mga active items sa kasalukuyang nakabukas na tab content
            const activeTabContent = document.querySelector('.tab-content.active');
            
            if (!activeTabContent) return;

            const items = activeTabContent.querySelectorAll('.option-item');

            items.forEach(item => {
                const textValue = item.getAttribute('data-search-value');
                if (textValue.includes(query)) {
                    item.classList.remove('hidden'); // Ipakita kapag match
                } else {
                    item.classList.add('hidden');    // Itago kapag hindi match
                }
            });
        }
    </script>
</body>
</html>