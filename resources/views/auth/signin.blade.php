<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
      background-color: #092B3B;
      color: white;
    }
    .form-wrapper {
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .form-container {
      background-color: #105A7E;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-wrap: wrap;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    .form-container img {
      max-width: 100%;
      height: auto;
    }
    .input-dark-blue {
      background-color: #E8F0FE;
    }
    .signInbtn {
      background-color: #092B3B;
      color: white;
      border: none;
    }
    .signInbtn:hover {
      background-color: #0b3f58;
    }
    .logo-below {
      margin-top: 2rem;
    }
    .bluishtext{
        color: rgba(255, 255, 255, 0.25);

    }
  </style>
</head>
<body>

  <div class="container form-wrapper">
    <div class="row justify-content-center">
      <div class="col-xl-8 col-lg-10 col-md-12">
        <div class="form-container d-flex">
          <!-- Left side image -->
          <div class="col-md-6 d-flex align-items-center justify-content-center p-3">
            <img src="./images/modon.png" alt="Company Logo" class="img-fluid">
          </div>

          <!-- Right side form -->
          <div class="col-md-6 p-4">
            <h1>System Login</h1>
            <p class="bluishtext">Kindly Login Using Previously Provided Email & Password</p>
            <form id="signinForm" class="pt-2">
              @csrf
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control input-dark-blue" id="email" name="email" value="momin@qltyss.com" placeholder="Email Here...." required />
              </div>
              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control input-dark-blue" id="password" value="admin1234" placeholder="**********" required />
              </div>
              <div class="d-grid p-2">
                <button type="submit" class="p-2 signInbtn fw-semibold">Sign In</button>
              </div>
              <div class="text-danger text-center" id="response-message"></div>
            </form>
          </div>
        </div>

        <!-- Bottom centered logo -->
        <div class="row justify-content-center logo-below">
          <div class="col-auto">
            <img src="./images/qsslogo.png" alt="QSS Logo" class="rounded img-fluid">
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
