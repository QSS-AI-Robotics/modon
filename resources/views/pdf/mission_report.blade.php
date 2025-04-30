<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mission Report</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            margin-bottom: 100px; /* Leave space for footer */
        }
        .header { margin-bottom: 20px; }
        .info { margin: 40px 0; }
        .info strong { width: 100%; display: inline-block; }
        .info span { width: 100%; display: inline-block; }

        .logo { width: 100%; margin-bottom: 20px; }
        .logo img { width: 140px; }
        .logo-table { width: 100%; border-collapse: collapse; }
        .logo-table td { width: 50%; }
        .logo-left {  }
        .logo-right { text-align: right; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-weight: bold;
        }
        .footer-table td {
            width: 33%;
            vertical-align: top;
            font-weight: bold;
        }
        .footer-left {  font-weight: bold;}
        .footer-center { text-align: center;font-weight: bold; }
        .footer-right {
    text-align: right;
    direction: ltr; /* ✅ Correct Arabic direction */
}

        /* Simple Bootstrap-like grid for images */
        .row { display: flex; flex-wrap: wrap; margin: 0 -5px; }
        .col-4 { flex: 0 0 33.3333%; max-width: 33.3333%; padding: 5px; }
        .text-center { text-align: center; }
        .container { width: 100%; padding: 0 15px; margin: auto; }
        .mb-4 { margin-bottom: 1rem; }
        .checkbox-group { margin: 10px 0; }
        .dotted-underline {
    width: 100%;
    border-bottom: 1px dotted #000;
    min-height: 30px;
    padding-bottom: 5px;
    margin-top: 5px;
    margin-bottom: 10px;
    text-transform: capitalize;
}
        .text-blue{
            color: #226D91;
        }
        .checkbox-group input[type="checkbox"] {
    transform: scale(1.2);
    margin-right: 5px;
}
    </style>
</head>

<body>

    <!-- Logos Section -->
    <div class="logo">
        <table class="logo-table">
            <tr>
                <td class="logo-left">
                    <img src="./images/qssblk.png" alt="QSS Logo" style="width:130px; height:40px">
                </td>
                <td class="logo-right">
                    <img src="./images/reprotmodon.png" alt="Mudon Logo" style="width:110px; height:65px">
                </td>
            </tr>
        </table>
    </div>

    <!-- Header -->
    {{-- <div class="header">
        <h2>Mission Report</h2>
    </div>
    <table style="width: 100%; border-collapse: collapse; direction: rtl;" >
        <tr class="dotted-underline">
           
            <th style="text-align: right;">اسم الشخص الذي طلب المهمة</th>
            <th style="width: 70%; text-align:left"> Name of the Person Who Requested the Mission</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['owner'] }}</td>
        </tr>
    
        <tr>
            
            <th style="text-align: right;">البرنامج الذي طلبت فيه المهمة</th>
            <th style="width: 70%; text-align:left">Program Under Which the Mission Was Requested</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['program'] }}</td>
        </tr>
    
        <tr>
           
            <th style="text-align: right;">المنطقة التي طلبت فيها المهمة</th>
            <th style="width: 70%; text-align:left">Region Where the Mission Was Requested</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['region'] }}</td>
        </tr>
    
        <tr>
          
            <th style="text-align: right;">موقع المهمة المطلوبة</th>
            <th style="width: 70%; text-align:left">Location of the Requested Mission</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['location'] }}</td>
        </tr>
    
        <tr>
   
            <th style="text-align: right;">الإحداثيات الجغرافية لموقع المهمة</th>
            <th style="width: 70%; text-align:left">Geo Coordinates of the Mission Location</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['geo'] }}</td>
        </tr>
    
        <tr>
    
            <th style="text-align: right;">اسم الطيار الذي نفذ المهمة</th>
            <th style="width: 70%; text-align:left">Name of the Pilot Who Completed the Mission</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['pilot'] }}</td>
        </tr>
    
        <tr>
          
            <th style="text-align: right;">ملاحظات إضافية تتعلق بالمهمة</th>
            <th style="width: 70%; text-align:left">Additional Notes Related to the Mission</th>
        </tr>
        <tr>
            <td colspan="2">{{ $data['description'] }}</td>
        </tr>
    </table> --}}
    
    <!-- Mission Information -->
    <div class="info">
        <strong>1. Name of the Person Who Requested the Mission:</strong> <br>
        <div class="dotted-underline">
            {{ $data['owner'] }}
        </div>
    </div>
    
    <div class="info">
        <strong>2. Program Under Which the Mission Was Requested:</strong><br>
        <div class="dotted-underline">
            {{ $data['program'] }}
        </div>
    </div>
    
    <div class="info">
        <strong>3. Region Where the Mission Was Requested:</strong><br>
        <div class="dotted-underline">
            {{ $data['region'] }}
        </div>
    </div>
    
    <div class="info">
        <strong>4. Location of the Requested Mission:</strong><br>
        <div class="dotted-underline">
            {{ $data['location'] }}
        </div>
    </div>
    
    <div class="info">
        <strong>5. Geo Coordinates of the Mission Location:</strong><br>
        <div class="dotted-underline">
            {{ $data['geo'] }}
        </div>
    </div>
    
    <div class="info">
        <strong>6. Name of the Pilot Who Completed the Mission:</strong><br>
        <div class="dotted-underline">
            {{ $data['pilot'] }}
        </div>
    </div>
    <div class="info">
        <strong>7. Mission Date:</strong><br>
        <div class="dotted-underline">
            {{ $data['missiondate'] }}
        </div>
    </div>
    <div class="info">
        <strong>8. Additional Notes Related to the Mission:</strong><br>
        <div class="dotted-underline">
            {{ $data['description'] }}
        </div>
    </div>


    <!-- Image Existence Checkboxes -->
    <div class="info">
        <strong>Images are there:</strong>
        <div class="checkbox-group">
            @if (!empty($data['images']) && count($data['images']) > 0)
                ☑️ Yes  ☐ No
            @else
                ☐ Yes  ☑️ No
            @endif
        </div>
    </div>
    
    

    <!-- Images Section -->
    @if (!empty($data['images']))
        <div style="page-break-before: always;"></div>

        <h3 style="text-align:center;">Mission Images</h3>

        <table width="100%" cellspacing="0" cellpadding="5">
            @foreach(array_chunk($data['images'], 3) as $imagesRow)
                <tr>
                    @foreach($imagesRow as $image)
                        <td style="text-align: center; padding: 10px;">
                            <img src="{{ public_path($image) }}" style="width: 100%; max-width: 180px; height: 160px; object-fit: contain; border: 1px solid #ccc; padding: 5px;">
                        </td>
                    @endforeach

                    {{-- Fill empty <td> if images are less than 3 in last row --}}
                    @for ($i = count($imagesRow); $i < 3; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        {{-- <table class="footer-table">
            <tr class="text-center">
                <td><img src="./images/reportFooter.png" alt="" class="w-50 "></td>
            </tr>

        </table> --}}
        <table class="footer-table">
            <tr>
                <td class="footer-left text-blue fw-bold">
                    Modon Headquarter<br>Al-Qurtubah District
                </td>
                <td class="footer-center text-blue fw-bold">
                    info@modon.sa<br>www.modon.sa
                </td>
                <td  class="footer-right text-blue">
                    
                        <p style="margin-left:10rem !important">مقر مدن، حي قرطبة </p>
                                               <p>الرياض ، المملكة العربية السعودية</p>
                  
                </td>
             
            </tr>
        </table>
    </div>

</body>
</html>
