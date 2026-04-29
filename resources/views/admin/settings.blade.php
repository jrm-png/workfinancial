<style>
            /* Main Content Area */
        .content { margin-left: 230px; padding: 30px; }

</style>
<body>
@include('layouts.app')

<div class="content">
    <div class="header-section">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; margin: 0;">System Control Panel</h1>
            <!-- <p style="color: #64748b;">Manage global submission windows and access permissions</p> -->
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <div class="section-card">
            <!-- <h3 style="margin-top:0;"><i class="fas fa-globe"></i> Global Window</h3> -->
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label class="form-label">Submission Start Date</label>
                    <input type="date" name="submission_start" class="form-input" value="{{ $settings->submission_start }}">
                </div>
                <div style="margin-bottom: 20px;">
                    <label class="form-label">Submission End Date</label>
                    <input type="date" name="submission_end" class="form-input" value="{{ $settings->submission_end }}">
                </div>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;">
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <label class="form-label" style="margin:0;">Data Viewing Access</label>
                        <small style="color:#64748b; display:block;">Allow users to see the Workplan List</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="is_viewing_open" {{ $settings->is_viewing_open ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Save Global Settings</button>
            </form>
        </div>

        <div class="section-card">
            <h3 style="margin-top:0;"><i class="fas fa-user-shield"></i> Division Overrides</h3>
            <p style="font-size: 13px; color: #64748b;">The following centers can still submit/view even if the submission is closed.</p>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                        <th style="padding: 10px; font-size: 12px; color: #94a3b8;">RESPONSIBILITY CENTER</th>
                        <th style="padding: 10px; font-size: 12px; color: #94a3b8;">NAME</th>
                        <th style="padding: 10px; font-size: 12px; color: #94a3b8; text-align: center;">OVERRIDE STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <td style="padding: 12px; font-weight: 600;">{{ $user->responsibility_center }}</td>
                        <td style="padding: 12px;">{{ $user->name }}</td>
                        <td style="padding: 12px; text-align: center;">
                            <button onclick="toggleOverride({{ $user->id }})" 
                                    class="btn-status {{ $user->can_override_submission ? 'active' : '' }}" 
                                    style="border:none; cursor:pointer; padding: 5px 15px; border-radius: 20px; font-size: 11px;">
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
</body>