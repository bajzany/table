(function() {
	var Table = {};

	Table.init = function () {
		var inputs = $(document).find(':input.searchTable');
		$.each(inputs, function (i, input) {
			$(this).on('change', function(e){
				Table.sendSearch(this);
			});
			var timeout;
			$(this).on('keydown', function(e){
				var target = this;
				clearTimeout(timeout);
				timeout = setTimeout(function(){
					Table.sendSearch(target);
				},1000)
			});
		});
	};

	Table.sendSearch = function(target) {
		var url;
		var link = target.getAttribute('data-url');
		var controlName = target.getAttribute('data-control');
		if (!link) {
			return;
		}
		link = Stage.validateUrl(link);
		url = new URL(link);

		var inputs = $(document).find(':input.searchTable');
		$.each(inputs, function (i, input) {
			var name = input.getAttribute('name');
			var value = input.value;

			url.searchParams.append(controlName + '-' + name, value);
		});

		if (url) {
			new Stage.Ajax({
				url: url.toString(),
				afterExecuteSnippets: function () {
					Table.init();
				}
			});
		}
	};

	Stage.App.addActionAfterExecuteSnippet('searchTable', function (Ajax) {
		Table.init();
	});

	Table.init();
})();
