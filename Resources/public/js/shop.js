$(document).ready(function(){
    var currentRequest = null;
    reload();
    $('#search-field')
        .on('keyup', function() {
            var $this = $(this);
            currentRequest = $.ajax({
                type: 'GET',
                data: 'q=' + $this.val(),
                url: $this.data('action-url'),
                beforeSend: function() {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function(data) {
                    $('#results-container').html(data);
                    history.pushState('data', '', $this.data('main-action-url') + '?q=' + $this.val());
                    reload();
                }
            })
        });
});

function loadSlider()
{
    $('#price_range')
        .slider({id: "price_range_slider"})
        .on('slideStop', function(event) {
            var $priceRangeWrapper = $('#price_range_wrapper');
            var values = event.value;
            var from = parseInt(values[0]);
            var to = parseInt(values[1]);
            var urlParser = document.createElement('a');
            urlParser.href = $priceRangeWrapper.data('url-placeholder');
            var parameters = urlParser.search.replace(/^\?/, '').split('&');
            var indexedParameters = {};
            for (var i in parameters) {
                var parts = parameters[i].split('=');
                if (parts[1] != undefined) {
                    indexedParameters[parts[0]] = parts.join('=');
                }
            }

            indexedParameters['price'] = 'price[]=' + from + '..' + to;

            var finalQuery = [];
            for (var j in indexedParameters) {
                finalQuery.push(indexedParameters[j]);
            }

            urlParser.search = finalQuery.join('&');
            window.location.href = urlParser.href;
        })
    ;
}

function reload()
{
    loadSlider();
}