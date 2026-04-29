<div id="draftsModal" style="display:none; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:white; margin: 5% auto; width: 60%; border-radius:12px; max-height: 80vh; overflow-y:auto; padding: 25px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;"><i class="fas fa-edit"></i> My Drafted Plans</h2>
            <button onclick="document.getElementById('draftsModal').style.display='none'" style="border:none; background:none; font-size:20px; cursor:pointer;">✕</button>
        </div>
        
        <div id="drafts-content">
            <p style="text-align:center; color:#64748b;">Loading drafts...</p>
        </div>
    </div>
</div>