<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'models/LogModel.php';
require_once ROOT . 'funcs/redis_event.fn.php';

class JDPhoneControl extends Control {

    const TRIGGER_TIME = 5;

    const LOG_INFO = 'JD_info';

    public function __construct() {
        parent::__construct();
    }

    public function index() {
		$orderId = 'P1615040121993151';

		// 查詢話費訂單
        $sql  = "select tel, money from hf_order where pt_order_id=?s and order_status=1";
        $orderData = DB::getLine($sql, [$orderId]);

		// 查詢話費訂單池
        $sql  = "select * from hf_order_pool where pt_order_id=?s and status=0 and is_use=0";
        $order_pool = DB::getLine($sql, [$orderId]);

		// 隨機從 ip 池中取 ip
		$sql  = "select ip, port from ip_pool order by rand() limit 1";
        $ip_pool = DB::getLine($sql);
		$this->rand_iP = mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);

		// 查詢有效小號資料
        // $sql  = "select id, webcookie from card where status=1 limit 1";
		$sql  = "select id, webcookie from card where id=22";
        $card = DB::getLine($sql);
		// $jddata = $this->kcpaytwo($orderData['tel'], $orderData['money'], $order_pool, $ip_pool['ip'], $ip_pool['port'], $card, '1');
		$jddata = $this->kcpay($orderData['tel'], $orderData['money'], $ip_pool['ip'], $ip_pool['port'], $card);

        // 訂閱事件
        // $route = "JDPhone:payCallBack:{$data}";
        // redis_event_add($route, self::TRIGGER_TIME);

		// $this->resp($jddata);
		echo $jddata;
		// header('Location:' . $jddata);
    }

    /**
     * 生成訂單號
     */
    public function generateOrderId() {
    }

    /**
     * 去 JD 查單
     */
    public function query() {
    }

    /**
     * 回調
     */
    public function notify() {
    }

	/**
	 * 
	 */
    public function kcpay($tel, $money, $proxy_ip, $proxy_port, $card) {		
		$ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36';

		// $csrfToken = strtoupper( md5( uniqid( mt_rand() ) ) );
		// $eid = 'QLFHATSSHGICEOZTUUMMLZFUCKZ3YTXCFEC4DJ5GI2RVAPOBIZD4U4LZVB' . $csrfToken;

		// $webcookie = $this->cookiearry( $this->trimall($card['webcookie']) );
		
		$cookie = $card['webcookie'];
		// $cookie = "pt_pin=".$webcookie['pt_pin'].";pt_key=".$webcookie['pt_key'].";csrfToken=".$csrfToken.";3AB9D23F7A4B3C9B=".$eid.";";

		$proxyip = $proxy_ip . ':' . $proxy_port;
		
		$Jdcobe = array(
			'code' => '0'
		);

		$wapWeiXinPay = $this->sendGetindex($tel, $proxyip, $cookie, $ua);
		return $wapWeiXinPay;
		// // 下單頁面 查詢金額 ID
		// $product = $this->sendPostproduct($tel, $proxyip, $cookie, $ua);
		// if ( isset($product['data']['']) )
		// return $product;

		// // 下單頁面 送出電話號碼 & 金額 ID
		// if ( isset($product['data']['']) )
		// return $product;

		// // 確認下單後 跳轉 JD 付款頁面
		// $pay = $this->pay($tel, $proxyip, $cookie, $ua);
		// return $pay;

		// JD 查詢電話號碼 & 金額 ID
		$product = json_decode ($this->sendPostproduct($tel, $proxyip, $cookie, $ua), true);
		if ( $product === null ) {
		    LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---号码检验失败");
		    //$Jdcobe['code']='2';//小号过期
		    //file_put_contents( $file, "---小号".$card['id']."---已过期", FILE_APPEND ); 
		    return $Jdcobe;
		}

		if ( $product['skuPrice']['skuList'] === null ) {
		    LogModel::log("---手机".$tel."---暂不支持该号段");
		    $Jdcobe['code'] = '3';//暂不支持该号段
		    return $Jdcobe;
		}

		// 充值話費頁面下單
		$Order = $this->sendGetsubmit($tel, $proxyip, $cookie, $money, $product, $csrfToken, $eid, $ua);
		if ( $Order['code'] == '0' ) { 
		    LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---获取不到订单号".$Order['msg']);
		    
			if ( $Order['msg'] == null ) {
		        $Jdcobe['code'] = '2';//小号过期
		        LogModel::log("---小号".$card['id']."---已过期");  
		    }

		    $needle = "销售火爆";//判断是否包含a这个字符
			$tmparray = explode($needle,$Order['msg']);
		    if ( count($tmparray) > 1 ) {
		        $Jdcobe['code'] = '2';//小号过期
		        LogModel::log("---小号".$card['id']."---已过期");
		    }

		    $needles = "运营商";//判断是否包含a这个字符
			$tmparrays = explode($needles,$Order['msg']);
		    if ( count($tmparrays) > 1 ) {
		        $Jdcobe['code'] = '3';//运营商升级
		        LogModel::log("---手机".$tel."---运营商升级");
		    }

		    return $Jdcobe;
		}
		// JD 收银台頁面 取訂單號
		$payId = $this->sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua);
		if ( $payId == null ) {
		    LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---获取不到京东PID");
		    return $Jdcobe;
		}
		return $payId;
		
		// for($x = 0; $x <= 5; ++$x) {
		// 	// JD 支付頁面 能使用的支付方式
		// 	$index = $this->sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua);
		// 	if ($index) {
		// 		break;
		// 	}
		// }
		// if ( !$index ) {
		//     LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---请求京东收银台失败");
		//     return $Jdcobe; 
		// }

		for($x = 0; $x <= 5; ++$x) {
		    if( $x % 2 === 0 ) { 
		        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1';  
		    }
			else {
		        $ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36';     
		    }

		    // 跳轉 JX 付款頁面
		    $wapWeiXinPay = $this->sendGetindex($tel, $proxyip, $cookie, $Order, $payId, $ua);
		    // if ( $wapWeiXinPay['code'] == '0' ) {
			if ($wapWeiXinPay) {
		        break;
		    }
		}
		return $wapWeiXinPay;

		// if ( $wapWeiXinPay['code'] === '0' ) {
		//     $Jdcobe = array(
		//         'code' 		 => '1',
		//         'tel' 		 => $tel,
		//         'card_id' 	 => $card['id'],
		//         'orderId' 	 => $Order['orderId'],
		//         'payId' 	 => $payId,
		//         'pay_urls'   => $wapWeiXinPay['deepLink'],
		//         'jdPrePayId' => $wapWeiXinPay['jdPrePayId']
		// 	);
		// 	LogModel::log("---小号".$card['id']."---下单成功");	
		// }

		// // 并发控制
		// $Jdcobe['code'] = '4';
		// LogModel::log("---小号".$card['id']."---获取不到链接 code : " . $wapWeiXinPay['code']);

		// return $Jdcobe;
	}

	public function cookiearry($query) {
		$queryParts = explode(';', $query);
		$params = array();
		foreach ($queryParts as $param) {
			$item = explode('=', $param);
			$params[$item['0']] = $item['1'];
		}

		return $params;
	}

	/**
	 * 删除空格
	 */
	public function trimall($str) {
	    $oldchar = array(" ", "　", "\t", "\n", "\r");
	    $newchar = array("", "", "", "", "");
	    return str_replace($oldchar, $newchar, $str);
	}

	/**
	 * [1]
	 * JD 查詢電話號碼 & 金額 ID
	 */
	public function sendPostproduct($tel, $proxyip, $cookie, $ua) {
		// jsonpCBKDD
		// $url     = 'https://m.jingxi.com/kmhf/getkmhfassetsinfo?skuId=1000518651&mobileNo=TaBtJBZhALihVTefZ%2BxsLiJ%2FY0Z0ICbETMTMbm7ETshzTTl6gTTBhUJQm13KoovaQA4JaR%2FXYZT8VbTLcBs1FG%2BmuArKGZYjJE2bGhkIq%2B85VwZEIX%2BcJ6pcNFVcKm4VSuIsvIMnq5zvSWCg0Pu4tXplCS%2Bgjo74Kc8PhGcKA8zGk98zg%2BbTyFqprxE2ZHXz0WAUX%2BGmoFXSCw6KmS776fflfGnCSFRqO5tp%2F3LvuO%2BH8aURSdEArVkivuT9%2BMH%2BEFAc6Wcp6DU5Rc3q2%2BNGb%2F%2BS%2BwS7MmdwinS0cZBX42hCstqHuZb9esmufSHDV6r2Gyp%2FcnljclOOw07PT4QfXw%3D%3D&index=2&pinGou=0&faceValue=3000&_=1629550384500&sceneval=2&g_login_type=1&callback=jsonpCBKH&g_ty=ls';
		// $referer = 'https://st.jingxi.com/';
		
		// jsonpCBKEE
		// $url     = 'https://m.jingxi.com/kmhf/calcprice?skuId=1000519837&jingdouNum=0&couponAmt=0&mobileNo=FcG30b%2BVUbGckVWzIa827VMNXwLjJi2FD7GbpL338tF93Kr2j7SnvL6%2BaVHSlHApkHo%2F0awJ4Mreer4j8T4w4n0RGmVXmCKMYz9CvK1iXNpJFFeqSA3tKr304uNiN%2B1B56joHTJLy%2FdLL8S22Go2%2FXFgfrrhKW6e4V%2BNieeZWYWfu8S9WpaSxY%2FpVWac2SXdxB%2F8b6NwVp0pimuaz0EUhSJn1SKEX8KTiZW9lEYBMDwnkc9M%2F%2FUNdtkP0dbmfMJX2KxvYGZxkHY4mCQpZDHgMnOU3ZfmbYsw4afR4uA5qKfpdSFw937159EaBw8%2B10y%2BizBz5wtAR%2BtiliuVjJ9TGw%3D%3D&index=1&faceValue=5000&_=1629548216836&sceneval=2&g_login_type=1&callback=jsonpCBKEE&g_ty=ls';
		// $referer = 'https://st.jingxi.com/';

		$url     = 'https://newcz.m.jd.com/newcz/product.json';
		$referer = 'https://newcz.m.jd.com/';

		$header_arr = array(
            'CLIENT-IP: '       . $this->rand_iP,
            'X-FORWARDED-FOR: ' . $this->rand_iP
		);

		$data = array(
			'mobile' => $tel
		);
		$data = urldecode( http_build_query($data) );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		// 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close($ch);

		if ($err) {
            LogModel::log($err, 'sendPostproduct');

            return $err;
        }

        LogModel::log($res, 'sendPostproduct');
		return $res;
	}

	/**
	 * [2]
	 * 充值話費頁面下單
	 */
	public function sendGetsubmit($tel, $proxyip, $cookie, $money, $product, $csrfToken, $eid, $ua) {
	    $Jdcobe = array(
			'code' => '0'
		);

		$skuList = $product['skuPrice']['skuList'];
		switch( intval($money) ) {
			case 30:
				$skuId   = $skuList['0']['skuId'];
				$jdPrice = $skuList['0']['jdPrice'];
			break;
			case 50:
				$skuId   = $skuList['1']['skuId'];
				$jdPrice = $skuList['1']['jdPrice'];
			break;
			case 100:
				$skuId   = $skuList['2']['skuId'];
				$jdPrice = $skuList['2']['jdPrice'];
			break;
			case 200:
				$skuId   = $skuList['3']['skuId'];
				$jdPrice = $skuList['3']['jdPrice'];
			break;
			case 300:
				$skuId   = $skuList['4']['skuId'];
				$jdPrice = $skuList['4']['jdPrice'];
			break;
			case 500:
				$skuId   = $skuList['5']['skuId'];
				$jdPrice = $skuList['5']['jdPrice'];
			break;
			default:
			LogModel::log('充值' . intval($money) . '元,暂不支持该金额!', 'sendGetsubmit');
				$Jdcobe['msg'] = '2';
				return $Jdcobe;
			break;
		}

	    $url     = "https://newcz.m.jd.com/newcz/submitOrder.action?mobile=" . $tel . "&newSkuId=" . $skuId . "&orderPrice=" . $jdPrice . "&onlinePay=" . $jdPrice * 0.01 . "&skuId=" . intval( $money ) . "&origin=&csrfToken=" . $csrfToken . "&loginStatus=true&eid=" . $eid;
	    $referer = 'https://newcz.m.jd.com';

		$header_arr = array(
            'CLIENT-IP:'       . $this->rand_iP,
            'X-FORWARDED-FOR:' . $this->rand_iP
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, false);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode( http_build_query($data) ));
		// 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close($ch);

		if ($err) {
            LogModel::log($err, 'sendGetsubmit');

            return $err;
        }

		LogModel::log($res, 'sendGetsubmit');

		$titleo = "/<title>(.*)<\/title>/";
		preg_match_all($titleo, $res, $titles);
		$title = $titles['1']['0'];
		if( $title !== '充值' ) {
			LogModel::log($err, 'sendGetsubmit');

			$codea = "/<h3>(.*)<\/h3>/";
			preg_match_all( $codea, $res, $codes );
			$orderId = $codes['1']['0'];    
			$Jdcobe['msg'] = '获取不了订单 : ' . $orderId;

			return $Jdcobe;
		}

		$orderIde = "/<input type=\"hidden\" id=\"orderId\" value=\"(.*)\"/";
		preg_match_all($orderIde, $res, $orderIds);
		$orderId = $orderIds['1']['0'];
		if( $orderId == null ) {
		    $Jdcobe['msg'] = '获取订单号为空';
		    return $Jdcobe;
		}

		$Jdcobe['code']    = '1';
		$Jdcobe['orderId'] = $orderId;
		$Jdcobe['jdPrice'] = $jdPrice;
		$Jdcobe['Referer'] = $url;
		$Jdcobe['url']     = $url;
		$Jdcobe['tel']     = $tel;
		$Jdcobe['pay']     = $jdPrice * 0.01;

		return $Jdcobe;
	}

	/**
	 * [3]
	 * JD 收银台頁面 取訂單號
	 */
	public function sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua) {
		$url = "https://newcz.m.jd.com/newcz/goPay.action?orderId=".$Order['orderId']."&onlinePay=".$Order['jdPrice']* 0.01."&origin=&mobile=".$tel;
		$referer = $Order['Referer'];

		$header_arr = array(
            'CLIENT-IP:'       . $this->rand_iP,
            'X-FORWARDED-FOR:' . $this->rand_iP
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, false);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode( http_build_query($data) ));
		// 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		$Headers = curl_getinfo($ch);
		curl_close($ch);

		if ($err) {
            LogModel::log($err, 'sendGetgoPay');

            return $err;
        }

		if ( $res !== $Headers ) {
		    if( $Headers['url'] !== $url ){

		        $arr = parse_url($Headers['url']);
		        $arr_query = $this->convertUrlQuery($arr['query']);
				LogModel::log($arr_query, 'sendGetgoPay');

		        return isset($arr_query['payId']) ? $arr_query['payId'] : null;
		    }
		}

		return null;
	}

	public function convertUrlQuery($query) {
		$queryParts = explode('&', $query);

		$item = null;
		$params = [];
		foreach($queryParts as $param) {
			$item = explode('=', $param);
			$params[ $item['0'] ] = $item['1'];
		}

		return $params;
	}

	/**
	 * [4] 
	 * JD 支付頁面 能使用的支付方式
	 */
	// public function sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua) {
	// 	$url 	 = 'https://pay.m.jd.com/newpay/index.action';
	// 	$referer = 'https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=' . $payId;

	// 	$header_arr = array(
    //         'CLIENT-IP:'       . $this->rand_iP,
    //         'X-FORWARDED-FOR:' . $this->rand_iP
	// 	);

	// 	$data = array(
	// 		'lastPage' => $Order['url'],
	// 		'appId'    => 'jd_m_chongzhi',
	// 		'payId'    => $payId,
	// 		'_format_' => 'JSON'
	// 	);

	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_REFERER, $referer);
    //     curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
    //     // 解析網頁
    //     // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	// 	// http
	// 	// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
	// 	// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
	// 	// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
	// 	// 设置请求方式
	// 	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	// 	// curl_setopt($ch, CURLOPT_POST, false);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode( http_build_query($data) ));
	// 	// 設置連線時間
	// 	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //     // 执行之后不直接打印出来
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     // 解决重定向问题
    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// 	// https 模擬驗證
    //     curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	// 	$res = curl_exec($ch);
    //     $err = curl_error($ch);
	// 	curl_close($ch);

	// 	if ($err) {
    //         LogModel::log($err, 'sendPostindex');

    //         // return $err;
	// 		return false;
    //     }

	// 	LogModel::log($res, 'sendPostindex');
	// 	// return $res;
	// 	return true;
	// }

	/**
	 * [4]
	 * 跳轉 JX 付款頁面
	 */
	// public function sendGetindex($tel, $proxyip, $cookie, $Order, $payId, $ua) {
	public function sendGetindex($tel, $proxyip, $cookie, $ua) {
		// $url     = 'https://m.jingxi.com/bases/orderlist/list?order_type=1&start_page=1&page_size=10&last_page=0&callback=dealListCbA&callersource=newbiz&traceid=1258677524121196933&t=1629729349542&g_ty=ls&g_tk=5381&sceneval=2';
			
		// $jxsid   = '16295631799087559236';
		// $url     = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $Order['orderId'] . '&jxsid=' . $jxsid;
		// $url     = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219247520098&jxsid=16295631799087559236';
		
		// 取得頁面詳情
		// $url     = 'https://m.jingxi.com/bases/orderdetail/orderinfo?deal_id=219247520098&callersource=newbiz&callback=detailFirCbA&traceid=1257251612158783193&t=1629563351154&g_ty=ls&g_tk=5381&sceneval=2';
		// $url     = 'https://m.jingxi.com/bases/orderdetail/orderinfo?deal_id=' . $Order['orderId'] . '&callersource=newbiz&callback=detailFirCbA&traceid=1257187118929845422&t=1629555843371&g_ty=ls&g_tk=5381&sceneval=2';

		// https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219183417548&jxsid=16295501668492645976
		// $url 	 = 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $Order['orderId'] . '&jxsid=' . $jxsid;
						// https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219183417548&jxsid=16295501668492645976
						// https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D219183417548%26jxsid%3D16295501668492645976
		// $url 	 = 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D' . $Order['orderId'] . '%26jxsid%3D' . $jxsid;
		
		// $referer = 'https://st.jingxi.com/order/orderlist_merge.shtml?jxsid=' . $jxsid . '&orderType=waitPay&source=pingou';
		
		// $url     = "https://pay.m.jd.com/index.action?functionId=wapWeiXinPay&body=%7B%22payId%22%3A%22" . $payId . "%22%2C%22appId%22%3A%22jd_m_chongzhi%22%7D&appId=jd_m_chongzhi&payId=" . $payId . "&_format_=JSON";
		// $referer = "https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=" . $payId;
		
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=rbq5c38dr2l4rpkixo3zsnxvvrevebl5mbbyejc6myadnuvl&token=AAEAMLR6V6EDVEMBXWALkb9A3DymedXfO9Ngo237-V-dvExb8n-3JVXq2sHeuzekI2XBsg
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMGxnvKV5Q43pu96m8dttoymoP_fU-VxxGly7usKkoAB3NbTaJPd6ghRxgxFenw00Sg
		
		// https://plogin.jd.hk/cgi-bin/mm/login?redirect=false&appid=233&lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMOCLgGPJQvXurlbFsy5tdtCPM-mHFTOVMu0zIWzk2VyXgsaMeXXxRyK0umjieq-M_w
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMIwhPw6TzsPU23TJt5yiAGb80pI61RGveH8Kg49IRvuQeGVS5zC37LaLxV8PQBM6GQ
		
		// https://jcapmonitor.m.jd.com/web_jcap_report?appID=&uid=&sid=R3u8cQABAAAAAAAAAAAAMDQI4HCEGw4gAy0iH438Mbuaj6HD7PmgWlVUw-z8XD_o61rDSAi6_jFURuD7ltjl9gAAAAA&interfaceId=268435460&fp=pguL8g2w-AvJCUhhdowTG_bZ_OEuI2XTQJC-YkQtmmWCqDL-QDd9VExiFemRygpyJBbekg%3D%3D&os=m&netType=unknown&status=0&callTime=232
		// https://plogin.jd.hk/cgi-bin/mm/login?redirect=false&appid=233&lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMJ5rykSFmqfl8mfrhg2VvvDkkYA49TX0ED0uirnTNvGf2zKhTwVdcz_SBaj1284cSw&
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMAfB28xmFGyr8cl0p4ZT_85GQ5aTJUAW-AFfJfcCLCq-kbO5RcKKXEkUYgzgHDHnLg
		
		// $url     = 'https://home.m.jd.com/myJd/newhome.action?sceneval=2&ufc=ebfb3790f238ddb4df14e0012ecada1e&';
		
		// $referer = 'https://st.jingxi.com/';

		$protocol = 'https:';
		$host     = 'st.jingxi.com';
		$search   = '?deal_id=220096880937&jxsid=16297337533918135784';
		$origin   = $protocol . "//" . $host;
		$pathname = '/order/n_detail_v2.shtml';

		$url      = $origin . $pathname . $search;
		$referer  = "{$origin}/order/orderlist_merge.shtml?jxsid=16297337533918135784&orderType=waitPay&source=pingou";

		$header_arr = array(
			'CLIENT-IP:'       . $this->rand_iP,
            'X-FORWARDED-FOR:' . $this->rand_iP
		);

		// $proxy = 'http://' . $proxyip;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, $proxy);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		// curl_setopt($ch, CURLOPT_POST, false);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode( http_build_query($data) ) );
		// 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close($ch);

		if ($err) {
            LogModel::log($err, 'sendGetindex');

            return $err;
			// return false;
        }

		$data = '{"errCode": "0","errMsg": "","baseInfo": {"appId":"wxae3e8056daea8727","pin":"jd_QuNePtSXyfLV","userType":"0","currentTime":"2021-08-27 21:20:01","keplerChannel":"0","kplTitleShow":"1"},"orderId": "220096880937","orderType": "37","sendPay": "00000000300000000005000002000000000000100000000000000000000001020000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000000000000000000001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000004100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000","specialDealList": ["isVirtual","isPhoneRecharge","isMobileRecharge"],"hasSubOrders": "0","parentId": "0","factPrice": "3000","factPriceTitle": "应付金额：","idPaymentType": "4","dateSubmit": "2021-08-27 13:11:21","orderCompleteTime": "","orderAreaId": "1_72_2819_0","payMethodList": ["payByWxH5","payByBank"],"stateInfo":{"stateCode":"1","stateName":"等待付款","stateCls":"bg_red","payLeftTime":"57020","stateTipList":["应付金额：¥30.00"]},"progressList": [],"shopList": [{"shopId": "11756","venderId": "11706","shopName": "京东充值","shopIcon":"icon_pop","shopImage":"https://img12.360buyimg.com/img/s40x40_jfs/t1/92233/26/19755/1782/5ea0194fEfa9c005c/fa406240530e25b6.png","shopLink":"","shopTotalNum":"1","productList": [{"skuId": "1000518090","skuUuid": "10_0_11706_1000518090_1_0","title": "湖南移动手机话费充值30元 快充","image": "https://img10.360buyimg.com/n2/g14/M03/17/19/rBEhV1MhZ70IAAAAAAPfALPpqJEAAKIvgMllyMAA98Y042.jpg","price": "3000","amount": "1","skuLink": "","skuDesc": "","specSkuDescList": [],"cids": "0,0,4833","skuPresaleSpotTip": "","cateIdList": [],"productBtnList": [],"tagList": []}],"contactList": [{"id": "contDd","name": "联系客服","link": "https://chat.jd.com/?venderId=1&entry=sq_order_jd&orderId=220096880937"}]}],"summaryList": [{"title": "订单编号：","content": "220096880937","extraInfo": {"id":"orderNumber"}},{"title": "下单时间：","content": "2021-08-27 13:11:21"},{"title": "支付方式：","content": "在线支付","extraInfo": {"id":"payway"}}],"billsList": [{"title": "商品总额","money": "¥ 30.00","extraInfo": {"id":"productPrice"}}],"buttonList" : [{"id": "payNormal","name": "去支付","link": "","isExtend": "0","extraInfo": {"type":"3"}}],"promptBarList": [{"colorCls": "","content": "请在下单后23.9小时内完成付款，超时未付款订单将被自动取消。","hasClose": "1"}],"extraInfo": {"paySwitch":"1","recycle":"0"},"specAddInfo":{"jdPhoneRecharge":{"dataList":[{"title": "充值号码：","content": "158****3254"},{"title": "号码归属：","content": "湖南移动"},{"title": "充值面额：","content": "¥ 30.00"}]}}}';

		$str_res     = "window.alert=console.log;";
		$str_replace = $str_res . "var detailData={$data};";
		// $str_replace .= "var host='{$host}';var href='{$url}';var search='{$search}';";
		// $str_replace .= "var protocol='{$protocol}';var pathname='{$pathname}';var origin='{$origin}';";
		$res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'stack:"servererror:"+(e||"").split("?")[0]';
		// $str_replace = 'stack:"test:"+e';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = '$.url.getUrlParam("sceneval");';
		// $str_replace = $str_res . 'console.log(i);console.log(c);console.log(s);console.log(d);';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'document.domain';
		// $str_replace = 'host';
		// $res = str_replace($str_res, $str_replace, $res);
		
		// $str_res     = 'document.referrer';
		// $str_replace = 'href';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'window.location.search';
		// $str_replace = 'search';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'window.location.href';
		// $str_replace = 'href';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'location.host';
		// $str_replace = 'host';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'location.href';
		// $str_replace = 'href';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'location.search';
		// $str_replace = 'search';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'location.protocol';
		// $str_replace = 'protocol';
		// $res = str_replace($str_res, $str_replace, $res);		

		// $str_res     = 'location.pathname';
		// $str_replace = 'pathname';
		// $res = str_replace($str_res, $str_replace, $res);

		$str_res     = ',loadInit();';
		$str_replace = '/*' . $str_res . '*/';
		$res = str_replace($str_res, $str_replace, $res);

		$str_res     = 'window.detailData';
		$str_replace = 'detailData';
		$res = str_replace($str_res, $str_replace, $res);

		$str_res     = '//wq.360buyimg.com/wxsq_trade/order/detail/js/detail.5696d502.js';
		$str_replace = '//boqing.win/detail.5696d502.js';
		$res = str_replace($str_res, $str_replace, $res);

		// $str_res     = '&callersource=mainorder';
		// $str_replace = '&callersource=newbiz';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'https://wq.jd.com';
		// $str_replace = 'https://m.jingxi.com';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'wq.jd.com/webmonitor';
		// $str_replace = 'm.jingxi.com/webmonitor';
		// $res = str_replace($str_res, $str_replace, $res);
		
		// $str_res     = '"//wq.360buyimg.com';
		// $str_replace = '"https://wq.360buyimg.com';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'src=\'';
		// $str_replace = 'src=\'https';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 't.crossorigin&&r.setAttribute("crossorigin","true"),';
		// $str_replace = '';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'crossorigin="true"';
		// $str_replace = '';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'crossorigin:e.crossOrigin';
		// $str_replace = '';
		// $res = str_replace($str_res, $str_replace, $res);
		
		// $str_res     = 'crossOrigin:';
		// $str_replace = 'test:';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'crossorigin:';
		// $str_replace = 'test:';
		// $res = str_replace($str_res, $str_replace, $res);
		
		// LogModel::log($res, 'sendGetindex');
		return $res;

		// if ( $res === null ) {
		//     return $res;
		// }

		// return json_decode($res, true);
	}

	/**
	 * 
	 * 跳轉 JX 付款頁面
	 */
	public function pay($tel, $proxyip, $cookie, $ua) {		
		$url 	 = 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D219064808941%26jxsid%3D16294363496241680153';
		$referer = 'https://st.jingxi.com';

		$header_arr = array(
            'CLIENT-IP:'       . $this->rand_iP,
            'X-FORWARDED-FOR:' . $this->rand_iP
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, $proxy);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		// curl_setopt($ch, CURLOPT_POST, true);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode( http_build_query($data) ) );
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		// 設置連線時間
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // 执行之后不直接打印出来
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 解决重定向问题
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// https 模擬驗證
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
        $err = curl_error($ch);
		curl_close($ch);

		if ($err) {
            LogModel::log($err, 'pay');

            return $err;
        }

		LogModel::log($res, 'pay');
		return $res;
	}

	/**
	 * [7]
	 * 獲取 JD 回調狀態
	 */
	public function verify($url, $proxyip, $payId, $order_id, $Cookie) {
		$data = "functionId=wapWeiXinPayQueryForMobile&body=%7B%22payId%22%3A%22" . $order_id . "%22%2C%22payEnum%22%3A%22407%22%2C%22time%22%3A%22%22%7D&appId=jd_m_chongzhi&payId=" . $payId . "&_format_=JSON";

		$Referer = "https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=" . $payId;
		$headerArray = array(
			"Cookie:" . $Cookie,
			"Referer:" . $Referer,
			"User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1"
		);
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" );

		curl_setopt( $curl, CURLOPT_PROXYTYPE, 0 ); //http
		curl_setopt( $curl, CURLOPT_PROXY, "http://" . $proxyip ); //代理服务器
		curl_setopt( $curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC ); //设置验证信息

		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headerArray );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
		$output = curl_exec( $curl );
		curl_close( $curl );

		if ( $output === false ) {
			return false;
		}

		$res = json_decode( $output, true );
		$res[ 'pay_time' ] = date( 'Y-m-d H:i:s', time() );

		LogModel::log($output, 'verify');
		return $res;
	}

	/**
	 * 無法使用
	 */
	public function kcpaytwo($tel,$money, $order_pool, $proxy_ip, $proxy_port, $card, $type) {
		$no = null;
		if ( $no % 2 === 0 ) {
			$ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1';
		}
		else {
			$ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36';
		}
		
		$csrfToken = md5( uniqid( mt_rand() ) );
		$eid = 'QLFHATSSHGICEOZTUUMMLZFUCKZ3YTXCFEC4DJ5GI2RVAPOBIZD4U4LZVB' . $csrfToken;

		$cookie = $card['webcookie'] . ';csrfToken=' . $csrfToken . ';3AB9D23F7A4B3C9B=' . $eid . ';';
		
		$proxyip = $proxy_ip . ':' . $proxy_port;

		$Jdcobe = array(
			'code' => '0'
		);

		// $type = '2';
		if ( $type === '2' ) {
			$Order = array(
				// 'orderId' => $order_pool['jd_order_id'],
				'orderId' => 123232,
				'jdPrice' => 10000,
				'url' 	  => 'https://newcz.m.jd.com/newcz/list.action?sid=1&sceneval=2&jxsid=16127881161083524619',
				'Referer' => ''
			);

			$payId = $this->sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua);
			if ( $payId == null ) {
				LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---二次下单获取不到京东PID");
				return $Jdcobe;
			}
			LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取PID成功");

			$index = null;
			for ($x = 0; $x <= 5; ++$x) {
				if ( $index = $this->sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua) ) {
					break;
				}
			}
			if ( !$index ) {
				LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---二次下单请求京东收银台失败");
				return $Jdcobe; 
			}
		}
		else {
		    $payId = $order_pool['jdpayid'];
		}

		$wapWeiXinPay = null;
		for ( $x = 0; $x <= 5; ++$x ) {
		    $wapWeiXinPay = $this->sendGetindex($tel, $proxyip, $cookie, $order_pool, $payId, $ua);

		    if ( $wapWeiXinPay[ 'code' ] == '0' ) {
				break;
		    }
		}
		
		if ( $wapWeiXinPay['code'] === '0' ) {
			LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取微信成功");

			$Jdcobe = array(
		        'code' => "1",
		        'card_id' => $card[ 'id' ],
		        'orderId' => $order_pool[ 'pay_order_id' ],
		        'payId' => $payId,
		        'pay_urls' => $wapWeiXinPay[ 'deepLink' ],
		        'jdPrePayId' => $wapWeiXinPay[ 'jdPrePayId' ]
				);
		}
		else {
			LogModel::log("---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取微信失败" . var_export($wapWeiXinPay, true) );
		}

		return $Jdcobe;
	}
}
