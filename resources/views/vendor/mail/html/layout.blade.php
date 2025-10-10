<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/TR/xhtml1/transitional" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>{{ config('app.name') }}</title>
<style type="text/css">
@media only screen and (max-width: 600px) {
    .inner-body {
        width: 100% !important;
    }
    .footer {
        width: 100% !important;
    }
}
@media only screen and (max-width: 500px) {
    .button {
        width: 100% !important;
    }
}
body {
    margin: 0;
    padding: 0;
    width: 100%;
    background-color: #f3f4f6;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}
.wrapper {
    width: 100%;
    margin: 0;
    padding: 0;
    background-color: #f3f4f6;
}
.content {
    width: 100%;
    margin: 0;
    padding: 20px 0;
}
.header {
    padding: 25px 0;
    text-align: center;
    background-color: #ffffff;
}
.header a {
    color: #1f2937;
    font-size: 24px;
    font-weight: bold;
    text-decoration: none;
}
.body {
    width: 100%;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
}
.inner-body {
    width: 570px;
    margin: 0 auto;
    padding: 40px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}
.content-cell {
    padding: 35px;
}
p {
    margin: 0 0 16px;
    font-size: 16px;
    line-height: 1.6;
    color: #374151;
}
.button {
    display: inline-block;
    background-color: #2563eb;
    border-radius: 8px;
    color: #ffffff !important;
    font-size: 16px;
    font-weight: 600;
    line-height: 50px;
    text-align: center;
    text-decoration: none;
    padding: 0 40px;
    margin: 20px 0;
}
.button:hover {
    background-color: #1d4ed8;
}
.footer {
    width: 570px;
    margin: 0 auto;
    padding: 20px 0;
    text-align: center;
}
.footer p {
    font-size: 14px;
    color: #6b7280;
    margin: 5px 0;
}
.footer a {
    color: #2563eb;
    text-decoration: none;
}
</style>
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{{ $header ?? '' }}
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}
{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>
{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>
