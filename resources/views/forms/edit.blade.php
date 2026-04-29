<!DOCTYPE html>
<html>
<head>
    <title>Edit Form {{ $form->form_rn }}</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/workplan.css') }}">
</head>
<body>

@include('layouts.app')

<div class="content">

    <h1>{{ $form->form_rn }}</h1>
    <p>Status: <strong>{{ strtoupper($form->status) }}</strong></p>

    {{-- TABS --}}
    <div class="tabs">
        <button onclick="showTab('work')">Work Plan</button>
        <button onclick="showTab('financial')">Financial Plan</button>
    </div>

    {{-- WORK PLAN --}}
    <div id="tab-work">
        @include('forms.partials.work-plan', ['form' => $form])
    </div>

    {{-- FINANCIAL PLAN --}}
    <div id="tab-financial" style="display:none;">
        @include('forms.partials.financial-plan', ['form' => $form])
    </div>

    {{-- ACTIONS --}}
    <form method="POST" action="{{ route('forms.submit', $form->id) }}">
        @csrf
        <button class="btn-submit">Submit for Approval</button>
    </form>

</div>

<script>
function showTab(tab) {
    document.getElementById('tab-work').style.display = tab === 'work' ? 'block' : 'none';
    document.getElementById('tab-financial').style.display = tab === 'financial' ? 'block' : 'none';
}
</script>

</body>
</html>
