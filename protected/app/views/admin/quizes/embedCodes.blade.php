@extends('admin/layout')

@section('content')

<h3>Copy this code to just above the "{{{ '</body>'}}}" tag</h3>
<textarea name="" id="" cols="60" rows="8">
<script>
    (function(){
		var objectName = '{{str_replace(array(" ", ".", "-", "_"), '', $_SERVER['HTTP_HOST']) . "-quizzesWidget"}}';
        window[objectName] = {
            showQuizzesList: function (container) {
                var containerId = 'quizWidget-' + (new Date()).getTime();
                container.setAttribute('id', containerId);
                container.innerHTML = '<iframe style="width:100%;" src="{{route('quizesIframe')}}?elm=' + encodeURIComponent('#' + containerId) + '&limit=' + container.getAttribute('data-limit') + '" frameborder="0"></iframe>';
                window.addEventListener('message', function(e){
                    var data = e.data;
                    try{
                        data = JSON.parse(data);
                        if(data.type == "quizzes-iframe-height-change") {
                            var containerElement = document.querySelector(data.element);
                            var iframe = containerElement.children[0];
                            iframe.style.height = parseInt(data.height) + "px";
                        }
                    } catch (ex){
                        console.log(ex.message);
                    }
                });
            }
        };
        var widgets = document.querySelectorAll('.x-quizzes-list-widget');
        for(var i = 0; i < widgets.length; i++){
            window[objectName].showQuizzesList(widgets[i]);
        }
    })();
</script>
</textarea>

<br><br>
<h3>Copy this code to wherever you want the quizzes list to appear</h3>
<textarea name="" id="" cols="60" rows="8">
<div class="x-quizzes-list-widget" data-limit="10"></div>
</textarea>
<br><br><br>

@stop