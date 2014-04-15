$(document).ready(function () {
    var entityChooser = $('#entity-chooser');
    entityChooser.entityselector({
        url: mw.util.wikiScript('api'),
        selectOnAutocomplete: true,
        type: 'item'
    });

    $(".button").on("click", function () {
        var $this = $(this);
        $this.siblings(".button").removeClass("selected");
        $this.addClass("selected");
    });

    $('#submit-button').on("click", function () {
        var $selected = $(".suggestion_evaluation .selected");
        var ratings = [];

        $selected.each(function () {
            var $this = $(this);
            var id = $this.parents("li").data("property");
            var label = $this.parents("li").data('label');
            var rating = $this.data('rating');
            ratings.push({'id': id, 'label': label, 'rating': rating });
        });

        console.log(ratings);

        var properties = [];
        $props = $(".property-entries li");
        $props.each(function () {
            var $this = $(this);
            var id = $this.data("property");
            var label = $this.data('label');
            properties.push({'id': id, 'label': label})
        });
        console.log(properties);
        var entry_id = $("input[name=qid]").val();
        submitJson(entry_id, properties, ratings);


    })
});
function getQuestionResults() {
    var overall = $('select[name=overall_exp]').val()[0];
    var like = $('textarea[name=like]').val();
    var missing = $('input[name=missing]').val();

    var question = {"overall": overall,
                    "positive": like,
                    "missing": missing};
    return question;
}

function submitJson(entry_id, properties, ratings) {
    var question = getQuestionResults();
    console.log(question);
    var evaluations = {
        entity: entry_id,
        properties: properties,
        suggestions: ratings,
        questions: question
    };


    console.log(evaluations);
    $('input[name=result]').val(JSON.stringify(evaluations));
    $('#form').submit();
}

//var id = k.parent("div").data("property");