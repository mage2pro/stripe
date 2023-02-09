<?php
namespace Dfe\Stripe\Facade;
# 2017-11-12
final class Token {
	/**
	 * 2017-11-12
	 * @used-by \Dfe\Stripe\Facade\Card::__construct()
	 * @used-by \Dfe\Stripe\Facade\Charge::tokenIsNew()
	 * @used-by \Dfe\Stripe\Method::cardType()
	 * @return bool
	 */
	static function isCard(string $id) {return df_starts_with($id, 'card_');}

	/**
	 * 2017-11-12
	 * @used-by \Dfe\Stripe\Facade\Charge::tokenIsNew()
	 * @used-by \Dfe\Stripe\Init\Action::sourceInitial()
	 * @used-by \Dfe\Stripe\Method::transUrlBase()
	 * @used-by \Dfe\Stripe\Payer::tokenIsSingleUse()
	 * @param string $id
	 * @return bool
	 */
	static function isPreviouslyUsedOrTrimmedSource($id) {return df_starts_with($id, 'src_');}

	/**
	 * 2017-10-22
	 * A new source (which is not yet attached to a customer) has the «new_» prefix,
	 * which we added by the Dfe_Stripe/main::tokenFromResponse() method.
	 * An example: «new_src_1BFV8vFzKb8aMux1ooPxEEar».
	 * @used-by \Dfe\Stripe\Facade\Customer::cardAdd()
	 * @used-by \Dfe\Stripe\Init\Action::sourceInitial()
	 * @used-by \Dfe\Stripe\P\_3DS::p()
	 * @used-by \Dfe\Stripe\P\Reg::v_CardId()
	 * @used-by \Dfe\Stripe\Payer::tokenIsSingleUse()
	 * @param string|null $id [optional]
	 * @return string
	 */
	static function trimmed($id = null) {return dfcf(function($id) {return df_trim_text_left(
		$id ?: \Df\Payment\Token::get(dfpm(__CLASS__)->ii()), 'new_'
	);}, [$id]);}
}