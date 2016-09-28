var config = {
	paths: {
		// 2016-03-02
		// Хитрый трюк: «?» позволяет избежать автоматического добаления расширения «.js».
		// https://coderwall.com/p/y4vk_q/requirejs-and-external-scripts
		'Dfe_Stripe/API': 'https://js.stripe.com/v2/?1'
	}
	,shim: {'Dfe_Stripe/API': {exports: 'Stripe'}}
};