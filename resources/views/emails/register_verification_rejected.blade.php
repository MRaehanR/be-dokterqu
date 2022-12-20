@component('mail::message')
<h1>Hello! {{ $name }}, <br> We have received your request {{ $role }} registration</h1>
<p>Thank you for taking the time to consider DokterQu. We wanted to let you know that we have chosen to move forward with a different candidate for the {{ $role }}.</p>
<p>We think you could be a good fit for other future openings and will reach out again if we find a good match.</p>

<p>Regards, <br> DokterQu</p>
@endcomponent