(function () {

	$(document).ready(function () {
		var entityChooser = $('#entity-chooser');
		entityChooser.entityselector({
			url: mw.util.wikiScript('api'),
			selectOnAutocomplete: true,
			type: 'item'
		});

		var propertyChooser = $('input[name=property-chooser]');
		propertyChooser.entityselector({
			url: mw.util.wikiScript('api'),
			selectOnAutocomplete: true,
			type: 'property'
		});

		$('#addButton').on("click", function () {
			var propertyName = propertyChooser.val();
			var pid = propertyChooser.next('input').val();
			if (propertyName !== '' && pid !== '') {
				var li_element = $('<li />');
				li_element.text(propertyName + ' (' + pid + ')');
				li_element.addClass('missing-properties');
				li_element.data('pid', pid);
				li_element.data('property-name', propertyName);
				var delete_link = $('<a href="#"> x </a>').click(pid, function () {
					$(this).closest('li').remove();
					return false;
				});
				li_element.append(delete_link);
				$("#missing-properties").append(li_element);
				propertyChooser.val("");
			}
		});
		$("#skip-button").on("click", function() {
			//window.location.reload(true);
			window.location = window.location.href;
		});
		$(".evaluation-button").on("click", function () {
			var $this = $(this);
			$this.siblings(".evaluation-button").removeClass("selected");
			$this.addClass("selected");

		});

		$('#submit-button').on("click", function () {
			var $selected = $(".suggestion_evaluation .selected");
			var ratings = [];

			$selected.each(function () {
				var $this = $(this);
				var $suggestion = $this.parents("li");
				var id = $suggestion.data("property");
				var label = $suggestion.data('label');
				var rating = $this.data('rating');
				var probability = $suggestion.data("probability");
				ratings.push({'id': id, 'label': label, 'rating': rating, 'probability': probability });
			});

			var properties = [];
			var missingProperties = [];

			var $missing_properties = $(".missing-properties");
			$missing_properties.each(function () {
				var entry = {};
				var $this = $(this);
				var property_id = $this.data("pid");
				var property_name = $this.data("property-name");
				entry[property_id] = property_name;
				missingProperties.push(entry);
			});
			var $props = $(".property-entries li");
			$props.each(function () {
				var $this = $(this);
				var id = $this.data("property");
				var label = $this.data('label');
				properties.push({'id': id, 'label': label})
			});
			var entry_id = $("input[name=qid]").val();
			submitJson(entry_id, properties, ratings, missingProperties);


		})
	});

	function getQuestionResults(missingProperties) {
		var overall = $('select[name = overall]').val()[0];
		var opinion = $('textarea[name = opinion]').val();

		var question = {
			"overall": overall,
			"opinion": opinion,
			"missing": missingProperties};
		return question;
	}

	function submitJson(entry_id, properties, ratings, missingProperties) {
		var question = getQuestionResults(missingProperties);
		var evaluations = {
			"entity": entry_id,
			"properties": properties,
			"suggestions": ratings,
			"questions": question
		};

		$('input[name=result]').val(JSON.stringify(evaluations));
		$('#form').submit();
	}


})();

//var id = k.parent("div").data("property");