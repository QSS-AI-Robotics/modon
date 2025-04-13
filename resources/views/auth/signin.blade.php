<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/signin.css') }}">

</head>
<body>
    <div class="container ">
        <div class="wrapper">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12   text-center ">
              <img src="./images/mudon.jpg"  alt="" class=" rounded w-75">
          </div>
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 p-4">
            <div class="row ">
                <form id="signinForm" class="pt-2 ">
                    @csrf
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 ">
                        <label for="exampleFormControlInput1" class="form-label">Email address</label>
                        <input type="email"  class="fldsty form-control InputField" id="email" name="email" value="nabeel@qltyss.com" placeholder="Email Here...." required/>
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 py-4">
                        <label for="exampleFormControlInput1" class="form-label">Password</label>
                        <input type="password" name="password" class="fldsty form-control InputField" id="password" value="admin1234"  placeholder="**********" required />
                    </div>
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 d-grid p-3 button">
                        <button type="submit" class="p-2 signInbtn" >SignIn</button>
                        <div class="text-danger text-center" id="response-message"></div>
                    </div>
                    
              </form>
              <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 text-center fw-bold p-2">
                 Powered By Quality Support Solutions
              </div>
            </div>
          </div>

        </div>
      </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    
    $('#signinForm').on('submit', function (e) {
        e.preventDefault();

        let formData = {
            email: $('#email').val(),
            password: $('#password').val(),
            _token: $('input[name="_token"]').val()
        };

        $.ajax({
            url: "/signin",
            type: "POST",
            data: formData,
            success: function (response) {
       
                window.location.href = response.redirect; // Redirect on success
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;
                console.log(errors);
               
                // Clear previous messages
                $('#email_error').text('');
                $('#password_error').text('');
                $('#response-message').text('').addClass('d-none');

                // Display validation errors
                if (errors) {
                    $('#email_error').text(errors.email ? errors.email[0] : '');
                    $('#password_error').text(errors.password ? errors.password[0] : '');
                }

                // Display general authentication error
                if (xhr.responseJSON.error) {
                    $('#response-message')
                        .text(xhr.responseJSON.error)
                        .removeClass('d-none');
                }
            }
        });
    });
</script>
</body>
</html>
