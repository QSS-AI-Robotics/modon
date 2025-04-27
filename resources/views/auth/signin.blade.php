<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/signin.css') }}">
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
            <h2>Modon Login</h2>
            <p class="bluishtext">Kindly Login Using Previously Provided <br> Email & Password</p>
            <form id="signinForm" class="pt-2">
              @csrf
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control input-dark-blue" id="email" name="email" value="adil@qltyss.com" placeholder="Email Here...." required />
              </div>
              <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control input-dark-blue" id="password" value="admin1234" placeholder="**********" required />
              </div>
              <div class="d-grid p-2 d-flex">
                <button type="submit" class="p-2 signInbtn w-25 fw-semibold d-flex align-items-center justify-content-center" id="signInBtn">
                  <span id="signInText">Log In</span>
                  <div id="spinner" class="spinner-border spinner-border-sm text-dark ms-2 d-none p-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </button>
                <div class="text-danger text-center ps-2 pt-2" id="response-message"></div>
              </div>
          
              <!-- Moved link outside the d-flex container -->
              <div class="text-end mt-3">
                <a href="#" id="forgetPasswordLink" style="color: #43839E;">Forgot Password?</a>
              </div>
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
    <!-- Forget Password Modal -->
    <div class="modal fade" id="forgetPasswordModal" tabindex="-1" aria-labelledby="forgetPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog" >
          <div class="modal-content"style="background: #105A7E">
              <div class="modal-header">
                  <h5 class="modal-title" id="forgetPasswordModalLabel">Reset Password</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <form id="forgetPasswordForm">
                      @csrf
                      <div class="mb-3">
                          <label for="forgetEmail" class="form-label">Email address</label>
                          <input type="email" class="form-control input-dark-blue" id="forgetEmail" name="email" placeholder="Enter your email" required>
                      </div>
                      <div class="text-danger" id="forgetPasswordError"></div>
                      <div class="text-success" id="forgetPasswordSuccess"></div>
                      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                  </form>
              </div>
          </div>
      </div>
  </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/signin.js') }}"> </script>
</body>
</html>
