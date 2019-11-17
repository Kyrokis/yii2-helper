var site = {};

(function () {
	
	/**
	 * Send message
	 */
	site.sendMessage = function () {
		$.get('/telegram/default/send-message', {
			'chat_id': $('#telegramform-chatId').val(),
			'text': $('#telegramform-text').val(),
		}).done(function (data) {
			console.log(data);
		});
		return false;
	};

	/**
	 * Get data
	 */
	site.getData = function () {
		$.get('/helper/default/get-data', {
			'link': $('#items-link').val(),
			'id_template': $('#items-id_template').val(),
			'offset': $('#items-offset').val(),
		}).done(function (data) {
			data = JSON.parse(data);
			console.log(data);
			$('#items-title').val(data.title);
			$('#items-link_img').val(data.link_img);
			$('#items-link_new').val(data.link_new);
			$('#items-now').val(data.now);
		});
		return false;
	};

	/**
	 * Let's helping
	 */
	site.helping = function () {
		$(this).text('Helping...').addClass('disabled');
		$.get('/helper/default/helping').done(function (data) {
			location.reload();
		});
		return false;
	};

	/**
	 * Check selected title
	 */
	site.check = function () {
		var item = $(this);
		var id = item.data('id');
		$.get('/helper/default/check', {'id': id}).done(function (data) {
			if (data) {
				var titleNew = item.parent().prev().prev().text();
				item.parent().prev().prev().prev().text(titleNew);
				item.parent().parent().removeClass('info');
				item.next().remove();
				item.remove();
			} else {
				console.log('Что-то пошло не так');
			}
		});
		return false;
	};

})();

$(function () {
	$('body').on('click', '.send-msg', site.sendMessage);
	$('body').on('click', '.get-data', site.getData);
	$('body').on('click', '.helping', site.helping);
	$('body').on('click', '.check', site.check);
});