            // Get all existing Local AWS Profiles
            function getAllAwsProfiles(){
                var currProfiles =  '';
                $.ajax({
                    url: "/api/v1/aws/profile/all",
                    type: "get",
                }).done(function (result) {
                    profiles = result + "";
                    console.log("==> Profiles List received : " + profiles);
                    var profileArr = profiles.split(',');
                    
                    for (var i=0; i< profileArr.length; i++) {
                        $('#selectAwsProfile').append('<option value="' + profileArr[i] + '">' + profileArr[i] + '</option>');
                    }
                    
                }).fail(function (jqXHR, textStatus, errorThrown) { 
                    console.log("Error while getting all profiles info : " + errorThrown);
                });
            }
            // get Current AWS profile
            function getCurrentProfile(){
                var currProfile =  '';
                $.ajax({
                    url: "/api/v1/aws/profile",
                    type: "get",
                }).done(function (result) {
                    console.log("==> Current AWS Profile : " + result);
                    setCurrentProfile(result);
                }).fail(function (jqXHR, textStatus, errorThrown) { 
                    console.log("Error while getting current AWS Profile Info: " + errorThrown);
                });
            }

            function setCurrentProfile(profile_name){
                $('#currentAwsProfile').html(profile_name);
            }

            // Draw a Table on Screen
            var Table; 
            function drawTableOnScreen(){
                Table = $("#servicesTable")
                .on( 'draw.dt', function () {
                    console.log( 'Loading' );
                    // Here show the loader.
                    $("#MessageContainer").html("Loading ...");
                })
                .DataTable({
                    data:[],
                    dom: 'Bfrtip',
                    buttons: [
                        'excel', 'pdf', 'print'
                    ],
                    iDisplayLength: -1,
                    columns: [
                                { "data": "VPCID"  },
                                { "data": "Service" },
                                { "data": "ServiceId or ARN" },
                                { "data": "Tag" },
                                { "data": "Public IP" },
                                { "data": "Private IP" },
                    ],
                    rowCallback: function (row, data) {},
                    filter: true,
                    info: true,
                    ordering: true,
                    processing: true,
                    retrieve: true        
                });
            }

            function showMessage (message){
                $('#messages').html(message);
                $('#messages').fadeOut(5000); // 5 secs
            }