/**
 * _apisearch can be anything or an array
 * at this point, if it is an array, we can collect all data inserted previously
 * and perform given track actions once the object is created
 *
 * _apisearch.push([app_id, token_id, user_id, item_id, item_type, weight])
 * _apisearch.push(['4378437', '3434-3432-432', '12345', '1', 'product', 10])
 */
var _apisearch = (function (ts) {

    function push(t) {
        var
            lastChild = document.body.lastChild,
            element = document.createElement('img'),
            interaction = {
                user: {'id': t[2]},
                item_uuid: {id: t[3], type: t[4]},
                weight: t[5]
            };


        element.src = 'http://localhost:8999/v1/interact?app_id=' + t[0] + '&token=' + t[1] + '&interaction=' + JSON.stringify(interaction);
        alert(element.src);
        element.height = 1;
        element.width = 1;
        element.style.display = 'none';
        lastChild.parentNode.insertBefore(element, lastChild);
    }

    for (var idx = 0; idx < ts.length; ++idx) {
        push(_apisearch[idx]);
    }

    return {
        push: push
    };

}(_apisearch || []));
