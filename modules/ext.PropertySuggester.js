
$( document ).ready(function (){
    var entityChooser = $( '#entity-chooser' );
	entityChooser.entityselector({
		url: mw.util.wikiScript( 'api' ),
		selectOnAutocomplete: true, 
		type: 'item'
	});


 /*   entityChooser.keyup(function (e) {
		if (e.keyCode === 13) {
			//getPropertiestoEntity()
		}
	});
	*/

    $(".button").on("click", function(event) {
        $this = $(this);
        $(this).siblings(".button").removeClass("selected")
        if ($this.hasClass("selected")) {
            $this.removeClass("selected");

        } else {
            $this.addClass("selected");
        }
        property_id = $this.parent("div").data("property");

    });

    $('#submit-button').on("click",function(event){
        $selected_good = $(".smile_button.selected");
        var positive = {};

       $selected_good.each(function () {
           id = $(this).parent("div").data("property");
           label = $(this).parent("div").data('label');
           positive[id]=label;
       });

        $selected_bad = $(".sad_button.selected");

        var negative={};
        $selected_bad.each(function () {
            id = $(this).parent("div").data("property");
            label = $(this).parent("div").data('label');
            negative[id]=label;
        });
        question = {};
        $selected_question = $(".question_button.selected");
        $selected_question.each(function() {
            id = $(this).parent("div").data("property");
            label = $(this).parent("div").data('label');
            question[id]=label;
        })

        var properties = {}
        $props = $(".properties_entry");
        $props.each(function () {
            id = $(this).data("property");
            label = $(this).data('label');
            properties[id] =label;
        })

        entry_id = $(".entry").data("entry-id");
        makeJSON(positive,negative,properties,entry_id,question);

        function makeJSON(positive,negative,properties,entry_id,question){

            var evaluations = {
                entity: entry_id,
                properties:[],
                suggestions: []
            }
            for (var x in positive){
                //console.log(x);
                evaluations.suggestions.push({id:x , label: positive[x],rating: "1"});
            }
            for (var y in negative){
                //console.log(x);
                evaluations.suggestions.push({id:y , label: negative[y],rating: "-1"});
            }
            for (var n in question){
                evaluations.suggestions.push({id:n , label: question[n],rating: "0"});
            }
            for (var z in properties){
                evaluations.properties.push({id:z , label: properties[z]});
            }
            console.log(evaluations);
            var property ={id: "",label:""};
            var suggestion = {id:"", label: "",rating : []};
          //      'properties' : [{id: P1, label: "wurst"},...],
        //   'suggestions': [ { id: P32, label:"hans", rating: [+1,0,-1], ...}

        }
    })
});

//var id = k.parent("div").data("property");