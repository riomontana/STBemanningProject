

$(function () { 
    var $customerDropDown = $('#drop-down-customers');
    var $allCustomers;
    var $chosenCustomer;
    var $userDropDown = $('#drop-down-users');
    var $allUsers;
    var $chosenUsers = [];
    var $submitButton = $('#submit-btn');
    var $resetButton = $('#reset-btn');
    var $latest_work_shift_id;
    var $chosenUsersTokens = [];
    var $workShift;
    var $date;
    var $shiftStart;
    var $shiftEnd;
    var $chosenUserId;

    $.ajax( {
        type: 'GET',
        url: 'api/v1/Api.php?apicall=getAllCustomers',
        success: function(response) { 
            var $response = JSON.parse(response);
            $.each($response, function(i, customerArray) {
                $allCustomers = customerArray;
                for(var count = 0; count < customerArray.length; count++) {
                    $customer_id = customerArray[count].customer_id;
                    $customer_name = customerArray[count].customer_name;
                    $customerDropDown.append('<option value="'+ count +'">' + $customer_name + ", id: " + $customer_id + '</option>');
                }
            });
        }
    });

    $.ajax( {
        type: 'GET',
        url: 'api/v1/Api.php?apicall=getAllUsersWithTokens',
        success: function(response) { 
            var $response = JSON.parse(response);
            $.each($response, function(i, userArray) {
                $allUsers = userArray;
                for(var count = 0; count < userArray.length; count++) {
                    $user_id = userArray[count].user_id;
                    $firstName = userArray[count].first_name;
                    $lastName = userArray[count].last_name;
                    $userDropDown.append('<option value="'+ count +'">' + $firstName + " " + $lastName + ", id: " + $user_id + '</option>');
                }
            });
            $userDropDown.attr('size',$allUsers.length);
        }
    });
    
    $customerDropDown.on('change', function() {
        var index = $(this).find('option:selected').index();
        if (index > 0) {
            $chosenCustomer = $allCustomers[index-1];
        } else {
            return;
        }
    });
    
    $userDropDown.on('change', function() {
        var index = $(this).find('option:selected').index();
        $(this).find('option:selected').attr("disabled", true);
        $chosenUsers.push($allUsers[index]);
    });
    
    $submitButton.on('click', function() {
        $date = $('#input-date').val();
        $shiftStart = $('#input-shift-start').val();
        $shiftEnd = $('#input-shift-end').val();
        $workShift = new Object();
        $workShift.shift_start = $date + " " + $shiftStart;
        $workShift.shift_end = $date + " " + $shiftEnd;
        
        if($chosenCustomer) {
            $workShift.customer_id = $chosenCustomer.customer_id;
        } else {
            alert("Du har inte valt något företag");
            return;
        }
        if($chosenUsers.length == 0) {
             alert("Du har inte valt någon anställd");
            return;
        }
        if(!$date) {
            alert("Du har inte val något datum");
            return;
        }
        
        $.ajax( {
            type: 'POST',
            url: 'api/v1/Api.php?apicall=createWorkShift',
            dataType: 'json',
            data: $workShift,
            success: function (data, textStatus, xhr) {
                console.log(data);
                var $response = data;
                $.each($response, function(i, workShiftArray) {
                    $workShiftArray = workShiftArray;
                    for(var count = 0; count < workShiftArray.length; count++) {
                        $latest_work_shift_id = workShiftArray[count].work_shift_id;
                        console.log($latest_work_shift_id);
                    }
                });
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log('Error in Operation');
                alert("Något gick tyvärr fel. Var vänlig försök igen.");
            },
            complete: function(){
                for(var i = 0; i < $chosenUsers.length; i++) {
                    var $specialWorkShift = new Object();
                    $specialWorkShift.user_id = $chosenUsers[i].user_id;
                    $specialWorkShift.work_shift_id = $latest_work_shift_id;
                    $chosenUserId = $chosenUsers[i].user_id
        
                    $.ajax( {
                        type: 'POST',
                        url: 'api/v1/Api.php?apicall=createSpecialWorkShift',
                        dataType: 'json',
                        data: $specialWorkShift,
                        success: function (data, textStatus, xhr) {
                            console.log(data);
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            console.log('Error in Operation');
                            alert("Något gick tyvärr fel. Var vänlig försök igen.");
                        }
                    });
                        
                    var notification = {
                        'title' : "Erbjudan om specialpass",
                        'message' : $chosenCustomer.customer_name +
                        ", " + $date + ", " + $shiftStart + "-" + $shiftEnd,
                        'user_id' : $chosenUserId
                    };
                    console.log(notification);
                    $.ajax( {
                        type: 'POST',
                        url: 'special-work-shift-operations/sendSinglePush.php',
                        dataType: 'json',
                        data: notification,
                        success: function (data) {
                            console.log(data);
                        }
                    });
                }
            alert("Notifikationer är skickade");
            location.reload();
            }
        });
    });
    
    $resetButton.on('click', function() {
        location.reload();
    });
 });
 
