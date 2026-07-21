<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Manage Users</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; color: #1e293b; }
        .sidebar { width: 190px; height: 100vh; background: #1e293b; color: white; position: fixed; padding: 20px; box-shadow: 4px 0 15px rgba(0,0,0,0.1); z-index: 100; }
        .nav-link { color: #94a3b8; font-size: 14px; padding: 10px 12px; display: flex; align-items: center; text-decoration: none; border-radius: 8px; transition: all 0.2s ease; margin-bottom: 5px; }
        .nav-link i { width: 22px; margin-right: 10px; font-size: 15px; }
        .nav-link:hover { background: #334155; color: #38bdf8; transform: translateX(3px); }
        .content { margin-left: 220px; padding: 40px; width: calc(100% - 220px); box-sizing: border-box; }

        .admin-card { 
            background: white; padding: 30px; border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
            width: 100%; box-sizing: border-box; margin-bottom: 30px;
        }

        .form-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #475569; }
        .form-input { 
            width: 100%; padding: 12px; border: 1px solid #cbd5e1; 
            border-radius: 8px; font-size: 14px; box-sizing: border-box; 
            transition: all 0.2s;
        }
        .form-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

        .user-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .user-table th { text-align: left; padding: 12px 15px; background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .user-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .user-table tr:hover { background-color: #f8fafc; }

        .alert-success { background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 8px; border: 1px solid #fca5a5; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        
        .btn-primary { background: #2563eb; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-primary:hover { background: #1d4ed8; }

        .btn-reset { color: #e11d48; background: #fff1f2; border: 1px solid #fecdd3; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
        .btn-reset:hover { background: #ffe4e6; color: #be123c; }

        .action-container { display: flex; gap: 8px; justify-content: center; align-items: center; }
        .btn-edit { color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; text-decoration: none; }
        .btn-edit:hover { background: #e0f2fe; color: #0369a1; }
        
        .btn-delete { color: #dc2626; background: #fef2f2; border: 1px solid #fee2e2; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; }
        .btn-delete:hover { background: #fee2e2; color: #991b1b; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .modal { display: none; position: fixed; z-index: 200; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .modal-content { background-color: white; border-radius: 12px; width: 600px; max-width: 90%; padding: 30px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative; animation: slideDown 0.3s ease; }
        .close-modal { position: absolute; top: 20px; right: 20px; font-size: 20px; color: #94a3b8; cursor: pointer; background: none; border: none; }
        .close-modal:hover { color: #475569; }


        .user-search-box {
            position: relative;
            width: 300px;
        }

        .user-search-box i {
            position: absolute;
            left: 14px;
            top: 13px;
            color: #94a3b8;
        }

        .user-search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
        }

        .user-search-box input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    @include('layouts.app')

    <div class="content">
        <div style="margin-bottom: 30px;">
            <h1 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Manage Users</h1>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- CREATE USER ACCOUNT CARD --}}
        <div class="admin-card">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-plus" style="color: #3b82f6;"></i> Create New User
            </h2>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="grid-2" style="margin-bottom: 20px;">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input name="name" class="form-input" placeholder="Enter full name..." required>
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="user@domain.com" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="">-- Select Role --</option>
                            <option value="PREPARER">PREPARER</option>
                            <option value="APPROVER">APPROVER</option>
                            <option value="REVIEWER">REVIEWER</option>
                            <option value="FINANCE">FINANCE</option>
                            <option value="DEPARTMENT MANAGER">DEPARTMENT MANAGER</option>
                            <option value="MONITOR">MONITOR (For PPIMD Only)</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2" style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                    <div>
                        <label class="form-label">Operating Department</label>
                        <select name="operating_department" id="dept-select" class="form-input" onchange="updateResponsibilityCenters('dept-select', 'rc-select')" required>
                            <option value="">-- Select Department --</option>
                            <option value="OGM">OGM (Office of the General Manager)</option>
                            <option value="ERD">ERD (Environmental Regulatory Division)</option>
                            <option value="RMDD">RMDD (Resource Management & Dev't Division)</option>
                            <option value="MSD">MSD (Management Services Division)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Responsibility Center</label>
                        <select name="responsibility_center" id="rc-select" class="form-input" required>
                            <option value="">-- Select Department First --</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus" style="margin-right: 8px;"></i> Create User Account
                    </button>
                </div>
            </form>
        </div>

        {{-- REGISTERED USERS TABLE --}}
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-users" style="color: #64748b;"></i> Registered Users
                </h2>

                <div class="user-search-box">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="userSearch"
                        placeholder="Search users..."
                        onkeyup="searchUsers()"
                    >
                </div>
            </div>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Department</th>
                        <th>Center</th>
                        <th>Role</th>
                        <th style="text-align: center; width: 300px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td style="font-weight: 600;">{{ $user->name }}</td>
                        <td style="color: #64748b;">{{ $user->email }}</td>
                        <td>{{ $user->operating_department ?? 'N/A' }}</td>
                        <td>{{ $user->responsibility_center ?? 'N/A' }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            <div class="action-container">
                                {{-- Edit Button --}}
                                <button type="button" class="btn-edit" onclick="openEditModal({{ json_encode($user) }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                {{-- Reset Password Button --}}
                                <form method="POST" action="{{ route('admin.users.reset', $user) }}" onsubmit="return confirm('Are you sure you want to reset this user\'s password?');" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-reset">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </form>

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('CRITICAL WARNING: Are you absolutely sure you want to delete this user? All associated records and permissions will be permanently removed.');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" {{ auth()->id() === $user->id ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ⭐ POPUP DYNAMIC EDIT USER MODAL --}}
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-cog" style="color: #0284c7;"></i> Update User Information
            </h2>
            
            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PUT')
                
                <div class="grid-2" style="margin-bottom: 20px;">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input name="name" id="edit-name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="edit-email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" id="edit-role" class="form-input" required>
                            <option value="PREPARER">PREPARER</option>
                            <option value="APPROVER">APPROVER</option>
                            <option value="REVIEWER">REVIEWER</option>
                            <option value="FINANCE">FINANCE</option>
                            <option value="DEPARTMENT MANAGER">DEPARTMENT MANAGER</option>
                            <option value="MONITOR">MONITOR (For PPIMD Only)</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2" style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                    <div>
                        <label class="form-label">Operating Department</label>
                        <select name="operating_department" id="edit-dept-select" class="form-input" onchange="updateResponsibilityCenters('edit-dept-select', 'edit-rc-select')" required>
                            <option value="OGM">OGM (Office of the General Manager)</option>
                            <option value="ERD">ERD (Environmental Regulatory Division)</option>
                            <option value="RMDD">RMDD (Resource Management & Dev't Division)</option>
                            <option value="MSD">MSD (Management Services Division)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Responsibility Center</label>
                        <select name="responsibility_center" id="edit-rc-select" class="form-input" required>
                            </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-reset" style="padding:12px 20px;" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-primary" style="background: #0284c7;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rcData = {
            'OGM': ['OGM', 'OAGM', 'SMO', 'PIU', 'IAD', 'LAD', 'PPIMD'],
            'ERD': ['CPD', 'ED', 'SMD', 'ECO'],
            'RMDD': ['PDMED', 'CDD', 'ELRD'],
            'MSD': ['ADMIN', 'FINANCE']
        };

        function updateResponsibilityCenters(deptSelectId, rcSelectId, selectedRcValue = '') {
            const deptSelect = document.getElementById(deptSelectId);
            const rcSelect = document.getElementById(rcSelectId);
            const selectedDept = deptSelect.value;

            rcSelect.innerHTML = '<option value="">-- Select Center --</option>';

            if (selectedDept && rcData[selectedDept]) {
                rcData[selectedDept].forEach(rc => {
                    const option = document.createElement('option');
                    option.value = rc;
                    option.textContent = rc;
                    if (selectedRcValue && rc === selectedRcValue) {
                        option.selected = true;
                    }
                    rcSelect.appendChild(option);
                });
            } else {
                rcSelect.innerHTML = '<option value="">-- Select Department First --</option>';
            }
        }

        function openEditModal(user) {
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');
            
            form.action = `/admin/users/${user.id}`;
            
            document.getElementById('edit-name').value = user.name;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-role').value = user.role;
            document.getElementById('edit-dept-select').value = user.operating_department || '';
            
            updateResponsibilityCenters('edit-dept-select', 'edit-rc-select', user.responsibility_center);
            
            modal.style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editUserModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function searchUsers() {
            const searchValue = document
                .getElementById('userSearch')
                .value
                .toLowerCase()
                .trim();

            const rows = document.querySelectorAll('.user-table tbody tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();

                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>