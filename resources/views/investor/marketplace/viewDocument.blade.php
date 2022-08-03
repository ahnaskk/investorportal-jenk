<!DOCTYPE html>
<html lang="en">
    <head>
        @if($ext == 'pdf')
        <style type="text/css">
            div.pdf {
                position: absolute;
                top: -42px;
                left: 0;width:100%;
            }
        </style>
        @else
        <style type="text/css">
            .bgimg {
                height: 1000px; width: 100%;
                background-repeat: no-repeat;
                background-position: top left; 
                position: relative;
                background-image: url({!! $fileName !!});
            }
        </style>
        @endif
    </head>
    <body>
        @if($ext == 'pdf')
        <div class="pdf">
            <iframe id="pdfFrame"  src="{{ $fileName }}#toolbar=0&navpanes=0&scrollbar=0" width="100%" height="900px">
            </iframe>
        </div>
        @else
        <div class="bgimg" >
        </div>
        @endif
        
        <script>
             document.addEventListener("contextmenu", function (e) {
                e.preventDefault();
            }, false);
        </script>
    </body>
</html>