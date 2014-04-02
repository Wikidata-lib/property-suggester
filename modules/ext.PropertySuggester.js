

var itemId = "";



        //action=&format=json&language=en&type=item&continue=0&search=d&_=1396365714038

function getPropertiestoEntity()
{ var entityChooser = $('#entity-chooser');
    var input_entity = entityChooser.val();
    console.log(input_entity);
    var eid = $("#entity-chooser").data("entityselector").selectedEntity().id;
    itemId = eid;
    console.log(eid);
    var url = mw.util.wikiScript( 'api' ) + '?action=wbgetentities&format=json&ids=' +
   itemId + '&limit=20&language=' + wgPageContentLanguage;
    $.get(url, function( data ) {
        $("#result.item").html("");
        $('#result-item').append('<h3>Chosen Item: '+ input_entity + '</h3>');
        $('#result-item').append('<h3>Properties:</h3>');

        var prop = data["entities"][eid]["claims"];

        $.each(prop, function (k,v) {
            //$('#result-item').append(JSON.stringify(k) + '<br>');
            var prop_url = mw.util.wikiScript( 'api' ) + '?action=wbgetentities&format=json&ids=' +
                k + '&limit=10&language=' + wgPageContentLanguage;
            $.get(prop_url, function( data ) {
                var suggestions = data['entities'];
                $.each(suggestions, function (k, v) {
                    $('#result-item').append(JSON.stringify(v["labels"][wgPageContentLanguage]["value"]) + '<br>');
                });
            });
        });
    });
    var url2 = mw.util.wikiScript( 'api' ) + '?action=wbsgetsuggestions&format=json&entity=' +
        itemId + '&limit=7&language=' + wgPageContentLanguage;
    $.get(url2, function( data ) {
        $('#result').html("");
        $('#result').html('<h3>Suggestions:</h3>');
        var suggestions = data['search'];
        $.each(suggestions, function (k, v) {
            var property_id= v["id"];
            //$('#result').append(JSON.stringify(v["id"]) + ":-) "+'<br>');
            var prop_url = mw.util.wikiScript( 'api' ) + '?action=wbgetentities&format=json&ids=' +
                property_id + '&limit=20&language=' + wgPageContentLanguage;
            $.get(prop_url, function( data ) {
                var suggestions = data['entities'];
                $.each(suggestions, function (k, v) {
                    $('#result').append(JSON.stringify(v["labels"][wgPageContentLanguage]["value"]) + '<br>');
                });
            });


        });
    });

}
$( document ).ready(function (){
        var entityChooser = $( '#entity-chooser' );
	entityChooser.entityselector({
		url: mw.util.wikiScript( 'api' ),
		selectOnAutocomplete: true, 
		type: 'item'
	});

    $( '#add-property-btn2' ).click(function() {
        getPropertiestoEntity();
    });

    entityChooser.keyup(function (e) {
		if (e.keyCode === 13) {
			getPropertiestoEntity()
		}
	});
	
});