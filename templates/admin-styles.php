<style>
    .wrap table {
        margin-top: 15px;
    }

    .wrap form {
        margin: 20px 0;
    }

    .notice {
        padding: 15px;
    }

    .loading {
        display: none;
        margin-left: 10px;
        vertical-align: middle;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-radius: 50%;
        border-top: 3px solid #3498db;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .webhook-status {
        margin-left: 10px;
        display: none;
    }

    /* Additional styles for better UI */
    .form-table th {
        width: 200px;
    }

    .form-table input[type="text"],
    .form-table input[type="number"] {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-table input[type="text"]:focus,
    .form-table input[type="number"]:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }

    .description {
        margin-top: 5px;
        color: #666;
    }
    .manual-sync-section,
    .auto-sync-info {
        margin-top: 20px;
        padding: 20px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }

    .manual-sync-section h2,
    .auto-sync-info h2 {
        margin-top: 0;
        color: #23282d;
        font-size: 1.3em;
    }

    .manual-sync-section p,
    .auto-sync-info p {
        margin-bottom: 15px;
        color: #555;
    }
</style>
