
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
        good_array=[];

       $selected_good.each(function () {
           var id = $(this).parent("div").data("property");
           good_array.push(id);
       })
        console.log(good_array);

        $selected_bad = $(".sad_button.selected");
        bad_array=[];
        $selected_bad.each(function () {
            var id = $(this).parent("div").data("property");
            bad_array.push(id);
        })
        console.log ("bad: "+  bad_array);

        function makeJSON(good_array,bad_array){
            var evaluations = {
                entity: "",
                properties:[],
                suggestions: []
            }

            var property ={id: "",label:""};
            var suggestion = {id:"", label: "",rating : []}
          //      'properties' : [{id: P1, label: "wurst"},...],
        //   'suggestions': [ { id: P32, label:"hans", rating: [+1,0,-1], ...}

            };


        }


    })
});

//var id = k.parent("div").data("property");