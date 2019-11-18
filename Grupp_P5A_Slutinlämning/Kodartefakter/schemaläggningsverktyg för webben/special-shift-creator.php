<?php
    include "includes/headerAndNavAdmin.php";
?>

<style>
    #drop-down-users {
        width : 250px;
    }
</style>

<body>
    <div class="container">
    <br>
    <form id="create-work-shift">
        <h1>Lägg till specialpass</h1>
                <hr>
                <p><b>Välj företag</b></p>
                <select id="drop-down-customers">
                    <option value=""><b>--välj företag--</b></option>
                </select>
                <br><br>
                <p><b>Datum:</b></p>
                <input type="date" name="date" id="input-date">
                <br><br>
                <p><b>Startar:</b></p>
                <input type="time" name="shift_start" value="08:00" id="input-shift-start">
                <br><br>
                <p><b>Slutar:</b></p>
                <input type="time" name="shift_end" value="17:00" id="input-shift-end">
                <br><br>
                <p><b>Välj anställda som ska notifieras</b></p>
                <select multiple id="drop-down-users">
                </select>
                <br><br>
        <input type="button" id="reset-btn" value="Nollställ formulär">
        <input type="button" id="submit-btn" value="Skapa specialpass och skicka notifikationer">
    </div>
    
    <script src="special-work-shift-operations/jquery-3.3.1.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="special-work-shift-operations/form_functions.js"></script>
    
    <?php
     include "includes/footerAdmin.php";
    ?>