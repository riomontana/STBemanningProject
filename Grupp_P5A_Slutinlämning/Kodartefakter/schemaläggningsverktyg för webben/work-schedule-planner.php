<?php
    include "includes/headerAndNavAdmin.php";
    include($_SERVER["DOCUMENT_ROOT"] . "/admin/calendar-operations/load.php");
    include($_SERVER["DOCUMENT_ROOT"] . "/admin/calendar-operations/insert.php");
    include($_SERVER["DOCUMENT_ROOT"] . "/admin/calendar-operations/delete.php");
    include($_SERVER["DOCUMENT_ROOT"] . "/admin/calendar-operations/update.php");
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="fullcalendar/fullcalendar.min.js"></script>
    <script src="fullcalendar/locale/sv.js"></script>
    <script> 

    $( document ).ready(function() {  
        var $chosenUser;
        var $allCustomers;
        var $chosenCustomer;
        var $userDropDown = $('#drop-down-users');
        var $users;
        var tempStart;
        var tempEnd;
        
        $.ajax( {
            type: 'GET',
            url: 'api/v1/Api.php?apicall=getAllCustomers',
            success: function(response) { 
                var $response = JSON.parse(response);
                $.each($response, function(i, customerArray) {
                    $allCustomers = customerArray;
                    
                });
            }
        });
        
        $.ajax( {
            type: 'GET',
            url: 'api/v1/Api.php?apicall=getAllUsers',
            success: function(response) { 
                var $response = JSON.parse(response);
                $.each($response, function(i, userArray) {
                    $users = userArray;
                    for(var count = 0; count < userArray.length; count++) {
                        $userId = userArray[count].user_id;
                        $firstName = userArray[count].first_name;
                        $lastName = userArray[count].last_name;
                        $userDropDown.append('<option value="'+ count +'">' + $firstName + " " + $lastName + " - id: " + $userId + '</option>');
                    }
                });
            }
        });
    
        $userDropDown.on('change', function() {
            $('#btn-delete-work-shifts').prop("disabled",false);
            var index = $(this).find('option:selected').index();
            $chosenUser = $users[index-1];
            
            $('#calendar').remove();
            $('#calendar-holder-div').append('<div id="calendar"></div>');
    
            if (index > 0) {
                    var userId = $chosenUser.user_id;
                    calendar = $('#calendar').fullCalendar( {
                        editable:true,
                        header:{
                            left:'prev,next today',
                            center:'title',
                            right:'month,agendaWeek,agendaDay'
                            },
                            events:{
                                url: 'calendar-operations/load.php',
                                type: 'POST',
                                data: {user_id: userId},
                                error: function() {
                                    alert('Något gick tyvärr fel när schemat skulle hämtas, vänligen ladda om sidan');
                                },
                            },
                            selectable:true,
                            selectHelper:true,
                            select: function(start, end, allDay) {
                                var start = $.fullCalendar.formatDate(start, "Y-MM-DD ");
                                var end = start;
                                var customer_id;
                                tempStart = start;
                                tempEnd = end;
                                
                                $('#create-work-shift-modal').remove();
                                
                                $('#modal-holder-div').append(
                                    '<div class="modal fade" id="create-work-shift-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">'
                                    +'<div class="modal-dialog" role="document">'
                                    +'<div class="modal-content">'
                                    +'<div class="modal-header">'
                                    +'<h5 class="modal-title" id="exampleModalLabel">Lägg till arbetspass</h5>'
                                    +'<button type="button" class="close" data-dismiss="modal" aria-label="Close">'
                                    +'<span aria-hidden="true">&times;</span>'
                                    +'</button>'
                                    +'</div>'
                                    +'<div class="modal-body">'
                                    +'<form id="create-work-shift">'
                                    +'<p><b>Välj företag</b></p>'
                                    +'<select id="drop-down-customers">'
                                    +'<option value=""><b>--inget företag valt--</b></option>'
                                    +'</select>'
                                    +'<br><br>'
                                    +'<p><b>Skift start:</b></p>'
                                    +'<input type="time" value="08:00" name="shift_start" id="input-shift-start">'
                                    +'<br><br>'
                                    +'<p><b>Skift slut:</b></p>'
                                    +'<input type="time" value="17:00" name="shift_end" id="input-shift-end">'
                                    +'<br><br>'
                                    +'<p><b>Dagar denna vecka: (max 7 dagar)</b></p>'
                                    +'<input type="number" min="1" max="7" value="1" name="days-forward" id="input-days-forward">'
                                    +'<br><br>'
                                    +'<p><b>Antal veckor framåt: (max 52 veckor) </b></p>'
                                    +'<input type="number" min="1" max="52" value="1" name="weeks-forward" id="input-weeks-forward">'
                                    +'</form>'
                                    +'</div>'
                                    +'<div class="modal-footer">'
                                    +'<button type="button" class="btn btn-secondary" data-dismiss="modal">Avbryt</button>'
                                    +'<button type="button" class="btn btn-primary" id="btn-add-work-shift">Lägg till arbetspass</button>'
                                    +'</div>'
                                    +'</div>'
                                    +'</div>'
                                    +'</div>'
                                );
                                    
                                for(var count = 0; count < $allCustomers.length; count++) {
                                    $customer_id = $allCustomers[count].customer_id;
                                    $customer_name = $allCustomers[count].customer_name;
                                    $('#drop-down-customers').append('<option value="'+ count +'">' + $customer_name + ", id: " + $customer_id + '</option>');
                                }
                                    
                                $('#create-work-shift-modal').modal('show');
                        
                                $('#drop-down-customers').on('change', function() {
                                    var index = $(this).find('option:selected').index();
                                    if (index > 0) {
                                        $chosenCustomer = $allCustomers[index-1];
                                        customer_id = $chosenCustomer.customer_id;
                                    } else {
                                        return;
                                    }
                                });
                            
                                $('#btn-add-work-shift').on('click', function() {
                                    if($chosenCustomer) {
                                        start = tempStart + $('#input-shift-start').val() + ':00';
                                        end = tempEnd + $('#input-shift-end').val() + ':00'; 
                                        var daysForward = $('#input-days-forward').val();
                                        var weeksForward = $('#input-weeks-forward').val();
                                        var startMoment = moment(start);
                                        var endMoment = moment(end);
                                        
                                        for(var i = 1; i <= weeksForward; i++) {
                                            for(var x = 1; x <= daysForward; x++) {
                                                $.ajax( {
                                                    url:'calendar-operations/insert.php',
                                                    type:"POST",
                                                    data:{title:customer_id, start:start, end:end, user_id:userId},
                                                    success:function() {
                                                        calendar.fullCalendar('refetchEvents');
                                                    }
                                                })
                                                if(daysForward > 1) {
                                                    startMoment.add(moment.duration(1, 'days'));
                                                    endMoment.add(moment.duration(1, 'days'));
                                                }
                                                start = moment(startMoment).format("Y-MM-DD HH:mm:ss");
                                                end = moment(endMoment).format("Y-MM-DD HH:mm:ss");
                                            }
                                            if(daysForward > 1) {
                                                startMoment.subtract(daysForward, 'days');
                                                endMoment.subtract(daysForward, 'days');
                                                start = moment(startMoment).format("Y-MM-DD HH:mm:ss");
                                                end = moment(endMoment).format("Y-MM-DD HH:mm:ss");
                                            }
                                            startMoment.add(moment.duration(1, 'weeks'));
                                            endMoment.add(moment.duration(1, 'weeks'));
                                            start = moment(startMoment).format("Y-MM-DD HH:mm:ss");
                                            end = moment(endMoment).format("Y-MM-DD HH:mm:ss");
                                        }
                                        
                                        $('#create-work-shift-modal').modal('hide');
                                    } else {
                                        alert("Välj ett företag");
                                    }
                                });
                            },
                            editable:true,
                            eventResize:function(event) {
                                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                                var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                                var id = event.id;
                                
                                $.ajax( {
                                    url:'calendar-operations/update.php',
                                    type:"POST",
                                    data:{start:start, end:end, id:id},
                                    success:function() {
                                        calendar.fullCalendar('refetchEvents');
                                    }
                                })
                            },
                            eventDrop:function(event) {
                                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                                var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                                var id = event.id;
                                
                                $.ajax( {
                                    url:'calendar-operations/update.php',
                                    type:"POST",
                                    data:{start:start, end:end, id:id},
                                    success:function() {
                                        calendar.fullCalendar('refetchEvents');
                                    }
                                });
                            },
                            eventClick:function(event) {
                                var date = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                                var dateFrom = date + " 00:00:00"; 
                                var dateTo = date + " 23:59:59"; 
                                
                                $('#delete-work-shift-modal').remove();
                                $('#modal-holder-div-delete').append(
                                    '<div class="modal fade" id="delete-work-shift-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">'
                                    +'<div class="modal-dialog" role="document">'
                                    +'<div class="modal-content">'
                                    +'<div class="modal-header">'
                                    +'<h5 class="modal-title" id="exampleModalLabel">Ta bort arbetspass</h5>'
                                    +'<button type="button" class="close" data-dismiss="modal" aria-label="Close">'
                                    +'<span aria-hidden="true">&times;</span>'
                                    +'</button>'
                                    +'</div>'
                                    +'<div class="modal-body">'
                                    +'<form id="delete-work-shift">'
                                    +'<p><b>Från: </b></p>'
                                    + '<p>' + date + '</p>'
                                    +'<p><b>Till:</b></p>'
                                    +'<input type="date" name="date-to" id="input-date-to" value="' + date + '">'
                                    +'</form>'
                                    +'</div>'
                                    +'<div class="modal-footer">'
                                    +'<button type="button" class="btn btn-secondary" data-dismiss="modal">Avbryt</button>'
                                    +'<button type="button" class="btn btn-primary" id="btn-delete-work-shift">Ta bort arbetspass</button>'
                                    +'</div>'
                                    +'</div>'
                                    +'</div>'
                                    +'</div>'
                                    );
                                $('#delete-work-shift-modal').modal('show');
                                
                                $('#btn-delete-work-shift').on('click', function() {
                                    var userId = $chosenUser.user_id;
                                    dateTo = $('#input-date-to').val() + " 23:59:59"; 
                                    
                                        $.ajax( {
                                            url:'calendar-operations/delete.php',
                                            type:"POST",
                                            data:{date_from:dateFrom, date_to:dateTo, user_id:userId},
                                            success:function() {
                                               calendar.fullCalendar('refetchEvents');
                                            }
                                        });
                                        $('#delete-work-shift-modal').modal('hide');
                                    
                                });
                            },
                            weekNumbers: true
                        });
                    } else {
                        return;
                    }
                });
            });
            
    </script>

        <br/>
        <div class="container">
            <h1>Schemaläggaren</h1>
            <hr>
            <select id="drop-down-users">
                <option value="">-- välj anställd --</option>
            </select>
            <button type="button" data-toggle="modal" data-target="#infoModal">
            Info
            </button>
           
        <div id="modal-holder-div-delete"></div> 
        <div id="modal-holder-div"></div> 
        
            <!-- info Modal -->
            <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Information</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul>
                                <li>Välj en anställd ur listan för att ladda ett schema</li>
                                <li>Funktioner för alla vyer: (månad, vecka, dag)</li>
                                    <ul>
                                        <li>Klicka på ett tomt datum för att lägga till ett arbetspass</li>
                                        <li>Klicka på ett arbetspass för att ta bort ett eller flera arbetspass</li>
                                        <li>Klicka, håll in och dra ett arbetspass för att flytta det till annan dag</li>
                                    </ul>
                                <li>Funktioner enbart för veckovy och dagvy:</li>
                                <ul>
                                    <li>Ändra tid på passet genom att flytta det uppåt eller nedåt</li>
                                    <li>Klicka och dra längst ned på passet för att göra det kortare eller längre</li>
                                </ul>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Stäng</button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div id="calendar-holder-div">
            </div>
        </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    
    <?php
     include "includes/footerAdmin.php";
    ?>