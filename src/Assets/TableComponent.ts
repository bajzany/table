if (module.hot) {
	module.hot.accept();
}

import {App, BaseComponent, SAGA_REDRAW_SNIPPET, AjaxOptions, Saga, validateUrl} from "Stage"

class TableComponent extends BaseComponent {


	constructor(App) {
		super(App)
	}

	initial() {
		super.initial();
		this.installPlugins();
	}

	@Saga(SAGA_REDRAW_SNIPPET)
	public installPlugins(action = null) {

		let target = document;
		if (action) {
			const {content} = action.payload;
			if (content) {
				target = content
			}
		}

		const the = this;

		let inputs = $(target).find(":input.searchTable");
		let sortings = $(target).find(".table-sortable");

		$.each(inputs, function (i, input) {
			$(this).on('change', function (e) {
				the.sendSearch(this);
			});
			let timeout;
			$(this).on('keydown', function (e) {
				let target = this;
				clearTimeout(timeout);
				timeout = setTimeout(function () {
					the.sendSearch(target);
				},1000)
			});
		});

		$.each(sortings, function (i, element) {
			$(this).on('click', function (e) {
				the.sendSort(this);
			});
		});
	}

	private sendSort(target) {
		let url;
		let link = target.getAttribute('data-url');
		let controlName = target.getAttribute('data-control');
		let name = target.getAttribute('data-name');
		let mode = target.getAttribute('data-mode');
		if (!link) {
			return;
		}
		link = validateUrl(link);
		url = new URL(link);

		switch (mode) {
			case 'ASC':
				mode = 'DESC';
				break;
			case 'DESC':
				mode = 'ASC';
				break;
			default:
				mode = 'ASC'
		}
		url.searchParams.append(controlName + '-' + name, mode);

		const table = $(target).closest('table');
		let sortings = $(table).find('.table-sortable');

		$.each(sortings, function (i, input) {
			if (input !== target) {
				let name = input.getAttribute('data-name');
				let mode = input.getAttribute('data-mode');
				if (mode !== 'undefined') {
					url.searchParams.append(controlName + '-' + name, mode);
				}
			}
		});

		if (url) {
			const options = AjaxOptions({
				url: url.toString()
			});
			$.ajax(options);
		}
	}

	private sendSearch (target) {
		let url;
		let link = target.getAttribute('data-url');
		let controlName = target.getAttribute('data-control');
		if (!link) {
			return;
		}
		link = validateUrl(link);
		url = new URL(link);


		const table = $(target).closest('table');
		let inputs = $(table).find(':input.searchTable');
		$.each(inputs, function (i, input) {
			let name = input.getAttribute('name');
			let value = input.value;

			url.searchParams.append(controlName + '-' + name, value);
		});

		if (url) {
			const options = AjaxOptions({
				url: url.toString()
			});
			$.ajax(options);
		}
	};

}

App.addComponent("TableComponent", TableComponent);
