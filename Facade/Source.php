<?php
namespace Dfe\Stripe\Facade;
use Df\Payment\Token;
// 2017-11-07
final class Source {
	/**
	 * 2017-10-22
	 * A new source (which is not yet attached to a customer) has the «new_» prefix,
	 * which we added by the Dfe_Stripe/main::tokenFromResponse() method.
	 * An example: «new_src_1BFV8vFzKb8aMux1ooPxEEar».
	 * @used-by \Dfe\Stripe\Facade\Customer::cardAdd()
	 * @used-by \Dfe\Stripe\P\Reg::v_CardId()
	 * @param string|null $id [optional]
	 * @return string
	 */
	static function trimmed($id = null) {return dfcf(function($id) {return
		df_trim_text_left($id ?: Token::get(dfpm(__CLASS__)->ii()), 'new_')
	;}, [$id]);}
}