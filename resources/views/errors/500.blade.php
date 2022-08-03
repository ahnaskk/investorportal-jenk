

<script
  src="https://browser.sentry-cdn.com/6.7.1/bundle.min.js"
  integrity="sha384-+GoWV2WnDUGsSmipBvyBrWuhW8hPa/D21fH+j3NoZxDf9RgDryqh4Ug4L+7E1nxM"
  crossorigin="anonymous"
></script>



<h1> Whoops! But dont worry we are working on it. </h1> 
<p>  Please do not close this page, and Please <a href="/" target="_blank">click here</a> to open investor portal in new tab!!  </p>




<div class="content">


  @if(app()->bound('sentry') && app('sentry')->getLastEventId())
   
   <p>Error ID: {{ app('sentry')->getLastEventId() }}</p>
    <script>
      Sentry.init({ dsn: '{{ config("sentry.dsn") }}' });
      Sentry.showReportDialog({
        eventId: '{{ app('sentry')->getLastEventId() }}'
      });
    </script>
  @endif
</div>



<style type="text/css">
  
  body {
  display: inline-block;
  background: #00AFF9 url(https://cbwconline.com/IMG/Codepen/Unplugged.png) center/cover no-repeat;
  height: 100vh;
  margin: 0;
  color: white;
}

h1 {
  margin: .8em 3rem;
  font: 4em Roboto;
}
p {
  display: inline-block;
  margin: .2em 3rem;
  font: 2em Roboto;
}


</style>