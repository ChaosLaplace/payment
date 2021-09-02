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
		// $orderId = 'P1615040121993151';

		// // 查詢話費訂單
        // $sql  = "select tel, money from hf_order where pt_order_id=?s and order_status=1";
        // $orderData = DB::getLine($sql, [$orderId]);

		// // 查詢話費訂單池
        // $sql  = "select * from hf_order_pool where pt_order_id=?s and status=0 and is_use=0";
        // $order_pool = DB::getLine($sql, [$orderId]);

		// // 隨機從 ip 池中取 ip
		// $sql  = "select ip, port from ip_pool order by rand() limit 1";
        // $ip_pool = DB::getLine($sql);

		// // 查詢有效小號資料
        // // $sql  = "select id, webcookie from card where status=1 limit 1";
		// $sql  = "select id, webcookie from card where id=22";
        // $card = DB::getLine($sql);
		// // $jddata = $this->kcpaytwo($orderData['tel'], $orderData['money'], $order_pool, $ip_pool['ip'], $ip_pool['port'], $card, '1');
		// $jddata = $this->kcpay($orderData['tel'], $orderData['money'], $ip_pool['ip'], $ip_pool['port'], $card);

        // // 訂閱事件
        // // $route = "JDPhone:payCallBack:{$data}";
        // // redis_event_add($route, self::TRIGGER_TIME);

		// 模擬用戶操作
		$this->deal_id = '220814300748';
		$this->jxsid   = '16304732969428955493';

		$cookie     = 'visitkey=63593404832403884; cid=3; retina=1; webp=1; sc_width=400; shshshfpa=b0538140-d607-84ed-03af-b5d70b600a9a-1629106422; shshshfpb=idJi93JSwcSQHlAb1GUwaUQ%3D%3D; wq_prior=4; pt_pin=jd_QuNePtSXyfLV; pwdt_id=jd_QuNePtSXyfLV; mba_muid=63593404832403884; downloadAppPlugIn_pgDownCloseDate=1629707274309_21600000; wxmall_ptype=2; PPRD_P=CT.138334.1.2-UUID.63593404832403884-FOCUS.FO4O305%3ABODAA14O601DA4O7A914O2FA43O23O1%3AFO4OEBOF1041BO19O97O3856O80104081CO7A91BODAA104351CA995C1C593C-EA.17008.1.14; share_cpin=; share_open_id=; share_gpin=; channel=; source_module=; erp=; block_call_jdapp=11; __jdc=68990090; wxa_level=1; wq_ug=11; __jda=68990090.63593404832403884.1629106421.1630412177.1630414011.84; __jxjda=68990090.63593404832403884.1629106421.1630411520.1630414011.75; jxsid=16304147477724753288; __jdv=68990090%7Cdirect%7Ct_1000072676_17008_001%7Cshouq%7C-%7C1630414747774; pt_key=AAJhLid1ADD6ptsMlGpWzwEz2QSOuMWFdJsYTvN9BSsn6zOUb3a87udxTRqkNr90NeQi-cFx4pg; pt_token=zwopfv1o; sfstoken=tk01ma1c81b72a8sMXgzeDJ4MVpKFhe7owAU3y/YJutc/0G8hGjMiZ1POiS7VikJIgZT4SnfHFvt9O648hdI0G9Rfc1s; jxsid_s_u=https%3A//st.jingxi.com/order/n_detail_v2.shtml; __wga=1630414844551.1630409703722.1630403579465.1629106421365.34.55; jxsid_s_t=1630414844684; shshshfp=26191b8a15d3e52d58672ed6130aec30';
		
		$rand_iP = mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
		$header_arr = array(
            'CLIENT-IP:'       . $rand_iP,
            'X-FORWARDED-FOR:' . $rand_iP
		);
		
		$ua = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36';

		// $jddata = $this->sendGetindex($cookie, $header_arr, $ua);
		// echo $jddata;

		// 有 cookie 就直接跳轉
		$this->url 	   = 'https://plogin.m.jd.com/cgi-bin/mm/new_login_entrance?';
		$this->referer = 'https://plogin.m.jd.com/login/login?appid=876&returnurl=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D' . $this->deal_id . '%26jxsid%3D' . $this->jxsid . '&source=wq_passport';
		
		$login_data = array(
			'lang' => 'chs',
			'appid' => 876,
			'returnurl' => 'https://st.jingxi.com/pingou/account/index.html?jxsid=' . $this->jxsid . '&ptag=138334.1.2',
			'risk_jd[eid]' => 'DRBH2XCKTML7NQUKOXRVJJ7DGUJTM2DYXEZ37TAWHBF6U3D6DTN372XPZJEZGMXDWGEG5RPKAZNP4PE3POIQVPTFBI',
			'risk_jd[fp]' => '6116fd22955a07c50ca1d0da6b5dd476'
		);
		foreach($login_data as $k => $v) {
			$this->url .= urlencode($k) . '=' . urlencode($v) . '&';
		}
		$this->url = trim($this->url, '&');

		$cookie = 'shshshfpa=77c7fbd4-b719-03b3-8293-00489fcd0a81-1628226481; shshshfpb=pc56Na6SjrMJYpXtC%2FY8y%2FA%3D%3D; __jdu=16282264726761156906948; unpl=V2_ZzNtbUBeShZ3CERcfRtcUmIGF14RB0EUdlxOVHIQVAEzAkBZclRCFnUUR11nGFsUZwEZXUFcQRxFCEZkexhdBWcEGlhKX3MldQlHVHoeVQZuASJeQmdCJXUPRV14GlsAYQAVW0VXQxFyAEdTcxxVNVcDGlpyV0IUdAtEUXgQVQFuMyJccldCFXQAdlRyGFwNbgoWVEJnFXt1CUdVehhZBmMLX11FVEoWdg9DUngeWgJnAxZaSlZEHXABdlVLGg%3d%3d; ipLoc-djd=53283-53300-0-0; _mkjdcnsl=110; mba_muid=16282264726761156906948; guid=fc03fb39d08a6c23708264d7e210c933c7b1f5cbc6d266d41de8a403fa486193; retina=1; webp=1; cartLastOpTime=1628950956; cartNum=0; visitkey=63593404832403884; kplTitleShow=1; sc_width=1920; jcap_dvzw_fp=pguL8g2w-AvJCUhhdowTG_bZ_OEuI2XTQJC-YkQtmmWCqDL-QDd9VExiFemRygpyJBbekg==; whwswswws=; qd_uid=KSEFUUDH-1NSSDN9W4LJJVPK86EF3; qd_fs=1629106347065; qd_ls=1629106347065; wxmall_ptype=2; _tp=mBrDcec1BmW%2F47n95VyaIw%3D%3D; _pst=chaoslaplace; TrackID=1QUsv5dnL4lVfcxYSHybD0dtvdRPsJ1Z3uL8N-QqUXbmIrRN45L4hIo67fvpiU6QOhhdTVFNOQ7pFGxJU7AdD1XNVXlI0HUyykwF1vljkcA4|||iJXULftXQPCjqEnlBhx3dA; unick=chaoslaplace; pinId=iJXULftXQPCjqEnlBhx3dA; qd_ts=1629265210620; qd_sq=2; deviceOS=; deviceVersion=92.0.4515.131; equipmentId=HAKSVPRQGX4PHH7VVQZTDX5H7TLOHSZN4EPUSHP6SCPQRVEVA36OGIRC4L2O33DL27GM2IXLX54AVD3AS4F6TI2DU4; fingerprint=b23d12c2281d8c8b5cbefd6696e8f5b5; deviceName=Chrome; deviceOSVersion=; abtest=20210818174219442_50; USER_FLAG_CHECK=ebfb3790f238ddb4df14e0012ecada1e; warehistory="10027628878675,"; sk_history=10027628878675%2C; __jdv=67544842%7Cst.jingxi.com%7C-%7Creferral%7C-%7C1629549667238; downloadAppPlugIn_downCloseDate=1629562487213_1800000; PPRD_P=UUID.16282264726761156906948; mcossmd=a45f38fffcbdd8287954cc99a6a31869; pinStatus=0; wq_prior=4; wq_ug=14; __jdc=67544842; lang=chs; channel=; share_cpin=; share_open_id=; share_gpin=; source_module=; erp=; wxa_level=1; jxsid=16303881308400161613; lsid=15ci9pxhnfm2tw71yggg6vbiruw465w8b9qlzn2m2oowlwxk; lstoken=7gwvzzxf; 3AB9D23F7A4B3C9B=HAKSVPRQGX4PHH7VVQZTDX5H7TLOHSZN4EPUSHP6SCPQRVEVA36OGIRC4L2O33DL27GM2IXLX54AVD3AS4F6TI2DU4; TrackerID=6NkiZQd1kAkIwO0I0AOR30LSZCZTOPSf6Vs4FssS-jUafL4O8bmz3TDlhmSg4shJeW6GMTuXctrvfkQjOH14lvU2bS-uZj2ygRfufC6PvB_QMpr9LMHhlfyv5Knaw-uuEdudNRIlAadEefsq8ySiwQ; pt_key=AAJhLid1ADD6ptsMlGpWzwEz2QSOuMWFdJsYTvN9BSsn6zOUb3a87udxTRqkNr90NeQi-cFx4pg; pt_pin=jd_QuNePtSXyfLV; pt_token=78omlnro; pwdt_id=jd_QuNePtSXyfLV; s_key=AAJhLid1ADBHldqYE_Y6IBDnaYX_1OzRGhqNAzU5HtcAa9sf3GAbvb5MVxbX-kxgc_TVklHtchc; s_pin=jd_QuNePtSXyfLV; sfstoken=tk01me27e1d2ca8sMXgyN0pIL2lvvSwXuNw+T0VnrvzdBrbb1XVeK4ontQhRQIBD7aLJLDUgouSxszSRXVs2xGFCv1NI; jxsid_s_u=https%3A//home.m.jd.com/myJd/newhome.action; cid=9; __wga=1630414742620.1630414712174.1630390989756.1628950956176.2.22; jxsid_s_t=1630414742670; rurl=https%3A%2F%2Fst.jingxi.com%2Fpingou%2Faccount%2Findex.html%3Fjxsid%3D16304147477724753288%26ptag%3D138334.1.2; shshshfp=e45388887cc5f5432b20743090188564; __jda=67544842.16282264726761156906948.1628226472.1630420411.1630473189.71; mobilePhone=15869853254; csrfToken=f2e24441f10d41ae85015555889b276c; RT="z=1&dm=jd.com&si=xxt68qix3tq&ss=kt11n7dt&sl=1&tt=0&obo=1&ld=1xb&r=3b5e3b133c04f1a522cea8e419298b52&ul=1xc&hd=23u"; _mkjdcn=131e5f8b93a6ad7d710f4c8d40b92c11; __jdb=67544842.5.16282264726761156906948|71.1630473189; mba_sid=16304731894864186529220275234.5; autoOpenApp_downCloseDate_auto=1630473205175_1800000; __jd_ref_cls=MCommonBottom_Find; wqmnx1=MDEyNjM3MnRxb294P2FvdDA3NDB6LnVyMHNsOGx0NkwgIC81ICAvMWZkLTVRT0YmKQ%3D%3D; wq_area=53283_53300_0%7C21';

		$returnurl     = $this->login0($cookie, $header_arr, $ua);

		$this->referer = $this->url;
		$this->url     = $returnurl;

		$cookie = 'visitkey=63593404832403884; retina=1; cid=3; webp=1; sc_width=400; shshshfpa=b0538140-d607-84ed-03af-b5d70b600a9a-1629106422; shshshfpb=idJi93JSwcSQHlAb1GUwaUQ%3D%3D; wq_prior=4; pt_pin=jd_QuNePtSXyfLV; pwdt_id=jd_QuNePtSXyfLV; mba_muid=63593404832403884; downloadAppPlugIn_pgDownCloseDate=1629707274309_21600000; wxmall_ptype=2; PPRD_P=CT.138334.1.2-UUID.63593404832403884-FOCUS.FO4O305%3ABODAA14O601DA4O7A914O2FA43O23O1%3AFO4OEBOF1041BO19O97O3856O80104081CO7A91BODAA104351CA995C1C593C-EA.17008.1.14; share_gpin=; share_open_id=; share_cpin=; erp=; source_module=; channel=; block_call_jdapp=11; __jdc=68990090; wxa_level=1; pt_key=AAJhLid1ADD6ptsMlGpWzwEz2QSOuMWFdJsYTvN9BSsn6zOUb3a87udxTRqkNr90NeQi-cFx4pg; pt_token=zwopfv1o; sfstoken=tk01ma1c81b72a8sMXgzeDJ4MVpKFhe7owAU3y/YJutc/0G8hGjMiZ1POiS7VikJIgZT4SnfHFvt9O648hdI0G9Rfc1s; shshshfp=7f0514db5955f50f22bf2d4ed4e11ae8; wq_area=53283_53300_0%7C21; jxsid=16304732969428955493; __jdv=68990090%7Cdirect%7Ct_1000072676_17008_001%7Cshouq%7C-%7C1630473296946; __jxjda=68990090.63593404832403884.1629106421.1630420104.1630473296.77; __jda=68990090.63593404832403884.1629106421.1630420104.1630473296.86; wq_ug=14; wqmnx1=MDEyNjM1MXQvamkvcmVfaGRpMDAmZDA5ODkzMDQ0bC5pIG8uZTVsQSBlaTcoTGtjQ2UwNSBsZjU2M1lkZjQzVlJERkgmUg%3D%3D; __wga=1630473323430.1630473296942.1630420104257.1629106421365.4.57; jxsid_s_t=1630473323474; jxsid_s_u=https%3A//st.jingxi.com/order/n_detail_v2.shtml; shshshsID=2f06f073900dd4fdafcdf49c93e2bdf0_4_1630473323610';
		echo $this->login1($cookie, $header_arr, $ua);

		// 取得登入參數
		// $login_data = $this->getLoginData($cookie, $header_arr, $ua);
		// echo $login_data;

		// 解析參數
		// $s = "/ak.rid\":(.*),\"ak.r/";
		// preg_match_all($s, $login_data, $token);
		// $s_token = $token['1']['0'];

		// $s = "/ak.ak\":(.*),\"ak.pv/";
		// preg_match_all($s, $login_data, $token);
		// $rsa_str = $token['1']['0'];

		// $login_data = array(
		// 	'mobile' => 'VbUpeYagdb8JkKOSr1VDwmy+bd0Css72yIjZTcRb71Ul4HxbHjXYpiqJLxIqZ+dy51ClEJvjSh85jBoEV9Vh/idGFU2QZngLTWDyieYYCrxIbXA+RQOtaAdWoc2WbT0kUIjMQdb4WqgDGOaBT39qTye5wutM52oDsFbSSYeWJrM=',
		// 	'country_code' => 971,
		// 	'checkcode' => 105096,
		// 	's_token' => '1cyevap4',
		// 	'risk_jd[eid]' => 'HAKSVPRQGX4PHH7VVQZTDX5H7TLOHSZN4EPUSHP6SCPQRVEVA36OGIRC4L2O33DL27GM2IXLX54AVD3AS4F6TI2DU4',
		// 	'risk_jd[fp]' => 'd1bfda5d5985506d3a8b26cdf2134ee6',
		// 	'risk_jd[sdkToken]' => '',
		// 	'risk_jd[token]' => 'VJEGVN2ZSIF5FCGUTBMEEBW5MINMQO6VVUHGYSWQ3RUUH2SMA7IOA3QLFXYGXUHLQPJAM34TWWYX6',
		// 	'risk_jd[jstub]' => 'TAVRHNOODDRO4TZUGHKKCXJD27V2JHVGYNTDNQIHWAG6YH77RNPB6GEIJEDUX4ALJCW4JBMXMALZWWR5DIJZDRECBQQP44VQ2B6CJVI'
		// );

		// $post_data = '';
		// foreach($login_data as $k => $v) {
		// 	$post_data .= urlencode($k) . '=' . urlencode($v) . '&';
		// }
		// $post_data = trim($post_data, '&');

		// 提交登入參數
		// echo $this->postLoginData($cookie, $header_arr, $ua, $post_data);

		// echo $this->login2($cookie, $header_arr, $ua);
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
		$ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1';

		$csrfToken = strtoupper( md5( uniqid( mt_rand() ) ) );
		$eid = 'QLFHATSSHGICEOZTUUMMLZFUCKZ3YTXCFEC4DJ5GI2RVAPOBIZD4U4LZVB' . $csrfToken;

		// $webcookie = $this->cookiearry( $this->trimall($card['webcookie']) );
		
		// $cookie = $card['webcookie'];
		// $cookie = "pt_pin=".$webcookie['pt_pin'].";pt_key=".$webcookie['pt_key'].";csrfToken=".$csrfToken.";3AB9D23F7A4B3C9B=".$eid.";";
		$cookie = 'shshshfpa=77c7fbd4-b719-03b3-8293-00489fcd0a81-1628226481; shshshfpb=pc56Na6SjrMJYpXtC%2FY8y%2FA%3D%3D; __jdu=16282264726761156906948; unpl=V2_ZzNtbUBeShZ3CERcfRtcUmIGF14RB0EUdlxOVHIQVAEzAkBZclRCFnUUR11nGFsUZwEZXUFcQRxFCEZkexhdBWcEGlhKX3MldQlHVHoeVQZuASJeQmdCJXUPRV14GlsAYQAVW0VXQxFyAEdTcxxVNVcDGlpyV0IUdAtEUXgQVQFuMyJccldCFXQAdlRyGFwNbgoWVEJnFXt1CUdVehhZBmMLX11FVEoWdg9DUngeWgJnAxZaSlZEHXABdlVLGg%3d%3d; ipLoc-djd=53283-53300-0-0; _mkjdcnsl=110; mba_muid=16282264726761156906948; guid=fc03fb39d08a6c23708264d7e210c933c7b1f5cbc6d266d41de8a403fa486193; retina=1; webp=1; cartLastOpTime=1628950956; cartNum=0; visitkey=63593404832403884; kplTitleShow=1; sc_width=1920; jcap_dvzw_fp=pguL8g2w-AvJCUhhdowTG_bZ_OEuI2XTQJC-YkQtmmWCqDL-QDd9VExiFemRygpyJBbekg==; whwswswws=; qd_uid=KSEFUUDH-1NSSDN9W4LJJVPK86EF3; qd_fs=1629106347065; qd_ls=1629106347065; wxmall_ptype=2; _tp=mBrDcec1BmW%2F47n95VyaIw%3D%3D; _pst=chaoslaplace; TrackID=1QUsv5dnL4lVfcxYSHybD0dtvdRPsJ1Z3uL8N-QqUXbmIrRN45L4hIo67fvpiU6QOhhdTVFNOQ7pFGxJU7AdD1XNVXlI0HUyykwF1vljkcA4|||iJXULftXQPCjqEnlBhx3dA; unick=chaoslaplace; pinId=iJXULftXQPCjqEnlBhx3dA; qd_ts=1629265210620; qd_sq=2; fingerprint=b23d12c2281d8c8b5cbefd6696e8f5b5; equipmentId=HAKSVPRQGX4PHH7VVQZTDX5H7TLOHSZN4EPUSHP6SCPQRVEVA36OGIRC4L2O33DL27GM2IXLX54AVD3AS4F6TI2DU4; deviceVersion=92.0.4515.131; deviceOS=; deviceOSVersion=; deviceName=Chrome; abtest=20210818174219442_50; USER_FLAG_CHECK=ebfb3790f238ddb4df14e0012ecada1e; warehistory="10027628878675,"; sk_history=10027628878675%2C; __jdv=67544842%7Cst.jingxi.com%7C-%7Creferral%7C-%7C1629549667238; downloadAppPlugIn_downCloseDate=1629562487213_1800000; PPRD_P=UUID.16282264726761156906948; mcossmd=a45f38fffcbdd8287954cc99a6a31869; pinStatus=0; wq_prior=4; wq_ug=14; __jdc=67544842; lang=chs; erp=; share_cpin=; share_open_id=; source_module=; channel=; share_gpin=; cid=9; __jda=67544842.16282264726761156906948.1628226472.1630230944.1630388114.63; wxa_level=1; jxsid=16303881308400161613; autoOpenApp_downCloseDate_auto=1630388131402_1800000; lsid=23jyz87k057bnnkznvj5dmgejmcfocoqf7offwzg27rpucpi; TrackerID=mbWgdrrFmeNo_tGyXDM3wLZ88ARec_9fnuPsSbvZNUlvJY2cO_GjYpt1z1zSg72p_Jocy7R1Jnmu9I3BjPgnKFgKybmEi3GlNgrpMpj2CcpIAy58b5y5m4q-3xT7wSkIQ-qJKsiVRY6meBIk0OQ97w; jxsid_s_u=https%3A//home.m.jd.com/myJd/newhome.action; csrfToken=83771819dca340308cfd9a80949fddd2; mobilev=html5; mobilePhone=18965724586; __wga=1630388210908.1630388182959.1630231076119.1628950956176.2.20; jxsid_s_t=1630388210956; wq_area=53283_53300_0%7C21; refer_v=real-url; rurl=https%3A%2F%2Fst.jingxi.com%2Fpingou%2Faccount%2Findex.html%3Fjxsid%3D16303882153126855527%26ptag%3D138334.1.2; _mkjdcn=9d552e4f584928f4548105b98db2fead; wqmnx1=MDEyNjM2MnRtbzM0M3o1aUFkICBsNXBiMygsICltMC5vUy82RjJuLTNRVU8qJkg%3D; __jdb=67544842.11.16282264726761156906948|63.1630388114; mba_sid=16303881143766085355466066182.11; shshshfp=4242f8fc4fef82f30a32a14e42f246ab; shshshsID=ab62f403d24789ac92ffa5d3de0c167e_4_1630388422324; lstoken=1cyevap4; 3AB9D23F7A4B3C9B=HAKSVPRQGX4PHH7VVQZTDX5H7TLOHSZN4EPUSHP6SCPQRVEVA36OGIRC4L2O33DL27GM2IXLX54AVD3AS4F6TI2DU4; __jd_ref_cls=MLoginRegister_VerificationInput; RT="z=1&dm=jd.com&si=z2hstedfg2r&ss=kszn0eko&sl=2&tt=0&obo=2&nu=d41d8cd98f00b204e9800998ecf8427e&cl=9kiv"';

		$proxyip = $proxy_ip . ':' . $proxy_port;
		
		$Jdcobe = array(
			'code' => '0'
		);

		// JD 查詢電話號碼 & 金額 ID
		$product = json_decode ($this->sendPostproduct($tel, $proxy_ip, $cookie, $ua), true);
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
		$Order = $this->sendGetsubmit($tel, $proxy_ip, $cookie, $money, $product, $csrfToken, $eid, $ua);
		return $Order;
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
	public function sendPostproduct($tel, $proxy_ip, $cookie, $ua) {
		// jsonpCBKDD
		// $url     = 'https://m.jingxi.com/kmhf/getkmhfassetsinfo?skuId=1000518651&mobileNo=TaBtJBZhALihVTefZ%2BxsLiJ%2FY0Z0ICbETMTMbm7ETshzTTl6gTTBhUJQm13KoovaQA4JaR%2FXYZT8VbTLcBs1FG%2BmuArKGZYjJE2bGhkIq%2B85VwZEIX%2BcJ6pcNFVcKm4VSuIsvIMnq5zvSWCg0Pu4tXplCS%2Bgjo74Kc8PhGcKA8zGk98zg%2BbTyFqprxE2ZHXz0WAUX%2BGmoFXSCw6KmS776fflfGnCSFRqO5tp%2F3LvuO%2BH8aURSdEArVkivuT9%2BMH%2BEFAc6Wcp6DU5Rc3q2%2BNGb%2F%2BS%2BwS7MmdwinS0cZBX42hCstqHuZb9esmufSHDV6r2Gyp%2FcnljclOOw07PT4QfXw%3D%3D&index=2&pinGou=0&faceValue=3000&_=1629550384500&sceneval=2&g_login_type=1&callback=jsonpCBKH&g_ty=ls';
		// $referer = 'https://st.jingxi.com/';
		
		// jsonpCBKEE
		// $url     = 'https://m.jingxi.com/kmhf/calcprice?skuId=1000519837&jingdouNum=0&couponAmt=0&mobileNo=FcG30b%2BVUbGckVWzIa827VMNXwLjJi2FD7GbpL338tF93Kr2j7SnvL6%2BaVHSlHApkHo%2F0awJ4Mreer4j8T4w4n0RGmVXmCKMYz9CvK1iXNpJFFeqSA3tKr304uNiN%2B1B56joHTJLy%2FdLL8S22Go2%2FXFgfrrhKW6e4V%2BNieeZWYWfu8S9WpaSxY%2FpVWac2SXdxB%2F8b6NwVp0pimuaz0EUhSJn1SKEX8KTiZW9lEYBMDwnkc9M%2F%2FUNdtkP0dbmfMJX2KxvYGZxkHY4mCQpZDHgMnOU3ZfmbYsw4afR4uA5qKfpdSFw937159EaBw8%2B10y%2BizBz5wtAR%2BtiliuVjJ9TGw%3D%3D&index=1&faceValue=5000&_=1629548216836&sceneval=2&g_login_type=1&callback=jsonpCBKEE&g_ty=ls';
		// $referer = 'https://st.jingxi.com/';

		$url     = 'https://newcz.m.jd.com/newcz/product.json';
		$referer = 'https://newcz.m.jd.com/';

		$header_arr = array(
            'CLIENT-IP: '       . $proxy_ip,
            'X-FORWARDED-FOR: ' . $proxy_ip
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
	public function sendGetsubmit($tel, $proxy_ip, $cookie, $money, $product, $csrfToken, $eid, $ua) {
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

		return $url;

		$header_arr = array(
            'CLIENT-IP:'       . $proxy_ip,
            'X-FORWARDED-FOR:' . $proxy_ip
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
		// curl_setopt($ch, CURLOPT_POST, false);
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
		return $res;

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
	 * 跳轉 JX 付款頁面
	 */
	// public function sendGetindex($tel, $proxyip, $cookie, $Order, $payId, $ua) {
	// public function sendGetindex($tel, $proxyip, $cookie, $ua) {
	public function sendGetindex($cookie, $header_arr, $ua) {
		// $url     = 'https://m.jingxi.com/bases/orderlist/list?order_type=1&start_page=1&page_size=10&last_page=0&callback=dealListCbA&callersource=newbiz&traceid=1258677524121196933&t=1629729349542&g_ty=ls&g_tk=5381&sceneval=2';
			
		// $this->jxsid   = '16295631799087559236';
		// $url     = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $Order['orderId'] . '&jxsid=' . $this->jxsid;
		// $url     = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219247520098&jxsid=16295631799087559236';
		
		// 取得頁面詳情
		// $url     = 'https://m.jingxi.com/bases/orderdetail/orderinfo?deal_id=219247520098&callersource=newbiz&callback=detailFirCbA&traceid=1257251612158783193&t=1629563351154&g_ty=ls&g_tk=5381&sceneval=2';
		// $url     = 'https://m.jingxi.com/bases/orderdetail/orderinfo?deal_id=' . $Order['orderId'] . '&callersource=newbiz&callback=detailFirCbA&traceid=1257187118929845422&t=1629555843371&g_ty=ls&g_tk=5381&sceneval=2';

		// https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219183417548&jxsid=16295501668492645976
		// $url 	 = 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $Order['orderId'] . '&jxsid=' . $this->jxsid;
						// https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=219183417548&jxsid=16295501668492645976
						// https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D219183417548%26jxsid%3D16295501668492645976
		// $url 	 = 'https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx20210809002430cddbd31b9031f0780000&package=609889655&redirect_url=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D' . $Order['orderId'] . '%26jxsid%3D' . $this->jxsid;
		
		// $referer = 'https://st.jingxi.com/order/orderlist_merge.shtml?jxsid=' . $this->jxsid . '&orderType=waitPay&source=pingou';
		
		// $url     = "https://pay.m.jd.com/index.action?functionId=wapWeiXinPay&body=%7B%22payId%22%3A%22" . $payId . "%22%2C%22appId%22%3A%22jd_m_chongzhi%22%7D&appId=jd_m_chongzhi&payId=" . $payId . "&_format_=JSON";
		// $referer = "https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=" . $payId;
		
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=rbq5c38dr2l4rpkixo3zsnxvvrevebl5mbbyejc6myadnuvl&token=AAEAMLR6V6EDVEMBXWALkb9A3DymedXfO9Ngo237-V-dvExb8n-3JVXq2sHeuzekI2XBsg
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMGxnvKV5Q43pu96m8dttoymoP_fU-VxxGly7usKkoAB3NbTaJPd6ghRxgxFenw00Sg
		
		// https://plogin.jd.hk/cgi-bin/mm/login?redirect=false&appid=233&lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMOCLgGPJQvXurlbFsy5tdtCPM-mHFTOVMu0zIWzk2VyXgsaMeXXxRyK0umjieq-M_w
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMIwhPw6TzsPU23TJt5yiAGb80pI61RGveH8Kg49IRvuQeGVS5zC37LaLxV8PQBM6GQ
		
		// https://jcapmonitor.m.jd.com/web_jcap_report?appID=&uid=&sid=R3u8cQABAAAAAAAAAAAAMDQI4HCEGw4gAy0iH438Mbuaj6HD7PmgWlVUw-z8XD_o61rDSAi6_jFURuD7ltjl9gAAAAA&interfaceId=268435460&fp=pguL8g2w-AvJCUhhdowTG_bZ_OEuI2XTQJC-YkQtmmWCqDL-QDd9VExiFemRygpyJBbekg%3D%3D&os=m&netType=unknown&status=0&callTime=232
		// https://plogin.jd.hk/cgi-bin/mm/login?redirect=false&appid=233&lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMJ5rykSFmqfl8mfrhg2VvvDkkYA49TX0ED0uirnTNvGf2zKhTwVdcz_SBaj1284cSw&
		// https://plogin.jingxi.com/cgi-bin/mm/login?lsid=na6alr0h47dtdebws6z0wgka6w4r6qvu115mt53ychrqw3mp&token=AAEAMAfB28xmFGyr8cl0p4ZT_85GQ5aTJUAW-AFfJfcCLCq-kbO5RcKKXEkUYgzgHDHnLg

		$protocol = 'https:';
		$host     = 'st.jingxi.com';
		$search   = '?deal_id=' . $this->deal_id . '&jxsid=' . $this->jxsid;
		$origin   = $protocol . '//' . $host;
		$pathname = '/order/n_detail_v2.shtml';

		// 取得跳轉頁面 url
		$url      = $origin . $pathname . $search;
		// header('Location:' . $url);

		// $url      = 'https://plogin.m.jd.com/login/login?appid=876&returnurl=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D' . $this->deal_id . '%26jxsid%3D' . $this->jxsid . '&source=wq_passport';
		$referer  = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $this->deal_id . '&jxsid=' . $this->jxsid;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_REFERER, $referer);
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
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
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

		// $data = '{"errCode": "0","errMsg": "","baseInfo": {"appId":"wxae3e8056daea8727","pin":"jd_QuNePtSXyfLV","userType":"0","currentTime":"2021-08-31 13:37:14","keplerChannel":"0","kplTitleShow":"1"},"orderId": "219925194485","orderType": "37","sendPay": "00000000300000000005000002000000000000100000000000000000000001020000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000000000000000000001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000004100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000","specialDealList": ["isVirtual","isPhoneRecharge","isMobileRecharge"],"hasSubOrders": "0","parentId": "0","factPrice": "3000","factPriceTitle": "应付金额：","idPaymentType": "4","dateSubmit": "2021-08-31 13:36:46","orderCompleteTime": "","orderAreaId": "1_72_2819_0","payMethodList": ["payByWxH5","payByBank"],"stateInfo":{"stateCode":"1","stateName":"等待付款","stateCls":"bg_red","payLeftTime":"86312","stateTipList":["应付金额：¥30.00"]},"progressList": [],"shopList": [{"shopId": "11756","venderId": "11706","shopName": "京东充值","shopIcon":"icon_pop","shopImage":"https://img12.360buyimg.com/img/s40x40_jfs/t1/92233/26/19755/1782/5ea0194fEfa9c005c/fa406240530e25b6.png","shopLink":"","shopTotalNum":"1","productList": [{"skuId": "1000525704","skuUuid": "10_0_11706_1000525704_1_0","title": "福建电信手机话费充值30元 快充","image": "https://img10.360buyimg.com/n2/g15/M06/0D/02/rBEhWVMhjAEIAAAAAAOOPF_0TssAAKAuAIpp_YAA45U425.jpg","price": "3000","amount": "1","skuLink": "","skuDesc": "","specSkuDescList": [],"cids": "0,0,4833","skuPresaleSpotTip": "","cateIdList": [],"productBtnList": [],"tagList": []}],"contactList": [{"id": "contDd","name": "联系客服","link": "https://chat.jd.com/?venderId=1&entry=sq_order_jd&orderId=219925194485"}]}],"summaryList": [{"title": "订单编号：","content": "219925194485","extraInfo": {"id":"orderNumber"}},{"title": "下单时间：","content": "2021-08-31 13:36:46"},{"title": "支付方式：","content": "在线支付","extraInfo": {"id":"payway"}}],"billsList": [{"title": "商品总额","money": "¥ 30.00","extraInfo": {"id":"productPrice"}}],"buttonList" : [{"id": "payNormal","name": "去支付","link": "","isExtend": "0","extraInfo": {"type":"3"}}],"promptBarList": [{"colorCls": "","content": "请在下单后23.9小时内完成付款，超时未付款订单将被自动取消。","hasClose": "1"}],"extraInfo": {"paySwitch":"1","recycle":"0"},"specAddInfo":{"jdPhoneRecharge":{"dataList":[{"title": "充值号码：","content": "189****4586"},{"title": "号码归属：","content": "福建电信"},{"title": "充值面额：","content": "¥ 30.00"}]}}}';

		// $str_res     = "window.alert=console.log;";
		// $str_replace = $str_res . "var detailData={$data};";
		// // // $str_replace .= "var host='{$host}';var href='{$url}';var search='{$search}';";
		// // // $str_replace .= "var protocol='{$protocol}';var pathname='{$pathname}';var origin='{$origin}';";
		// $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'stack:"servererror:"+(e||"").split("?")[0]';
		// // $str_replace = 'stack:"test:"+e';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = '$.url.getUrlParam("sceneval");';
		// // $str_replace = $str_res . 'console.log(i);console.log(c);console.log(s);console.log(d);';
		// // $res = str_replace($str_res, $str_replace, $res);

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

		// $str_res     = ',loadInit();';
		// $str_replace = '/*' . $str_res . '*/';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = 'window.detailData';
		// $str_replace = 'detailData';
		// $res = str_replace($str_res, $str_replace, $res);

		// $str_res     = '//wq.360buyimg.com/wxsq_trade/order/detail/js/detail.5696d502.js';
		// $str_replace = '//boqing.win/detail.5696d502.js';
		// $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = '&callersource=mainorder';
		// // $str_replace = '&callersource=newbiz';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'https://wq.jd.com';
		// // $str_replace = 'https://m.jingxi.com';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'wq.jd.com/webmonitor';
		// // $str_replace = 'm.jingxi.com/webmonitor';
		// // $res = str_replace($str_res, $str_replace, $res);
		
		// // $str_res     = '"//wq.360buyimg.com';
		// // $str_replace = '"https://wq.360buyimg.com';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'src=\'';
		// // $str_replace = 'src=\'https';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 't.crossorigin&&r.setAttribute("crossorigin","true"),';
		// // $str_replace = '';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'crossorigin="true"';
		// // $str_replace = '';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'crossorigin:e.crossOrigin';
		// // $str_replace = '';
		// // $res = str_replace($str_res, $str_replace, $res);
		
		// // $str_res     = 'crossOrigin:';
		// // $str_replace = 'test:';
		// // $res = str_replace($str_res, $str_replace, $res);

		// // $str_res     = 'crossorigin:';
		// // $str_replace = 'test:';
		// // $res = str_replace($str_res, $str_replace, $res);
		
		LogModel::log($res, 'sendGetindex');
		return $res;

		// if ( $res === null ) {
		//     return $res;
		// }

		// return json_decode($res, true);
	}

	/**
	 * [5] 
	 * 模擬登入
	 */
	public function login0($cookie, $header_arr, $ua) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		// curl_setopt($ch, CURLOPT_POST, false);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
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
            LogModel::log($err, 'login0');

            return $err;
        }

		$res = explode(' ', $res);
		$res = explode('"', $res['15']);

		LogModel::log($res['1'], 'login0');

		// if ( isset($res['1']) ) {
		// 	header('Location:' . $res['1']);
		// 	exit();
		// }

		return $res['1'];
	}

	/**
	 * [5] 
	 * 模擬登入
	 */
	public function login1($cookie, $header_arr, $ua) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        // 解析網頁
        // curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		// http
		// curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
		// curl_setopt($ch, CURLOPT_PROXY, "http://" . $proxyip);
		// curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		// 设置请求方式
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		// curl_setopt($ch, CURLOPT_POST, false);
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
            LogModel::log($err, 'login1');

            return $err;
        }

		LogModel::log($res, 'login1');
		return $res;
	}

	/**
	 * [5] 
	 * 模擬登入
	 */
	public function login2($cookie, $header_arr, $ua) {
		$url 	 = 'https://st.jingxi.com/order/n_detail_v2.shtml?deal_id=' . $this->deal_id . '&jxsid=' . $this->jxsid;
		$referer = 'https://st.jingxi.com/';

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
		// curl_setopt($ch, CURLOPT_POST, false);
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
            LogModel::log($err, 'login2');

            return $err;
        }

		LogModel::log($res, 'login2');
		return $res;
	}

	/**
	 * [5] 
	 * 模擬登入
	 */
	public function getLoginData($cookie, $header_arr, $ua) {
		$url 	 = 'https://plogin.jingxi.com/cgi-bin/mm/login?lsid=23jyz87k057bnnkznvj5dmgejmcfocoqf7offwzg27rpucpi&token=AAEAMAhPJps4-xWb0rh_Kv5P0TEyLjhlN8Z2c99DBkvPg2MFKV08-mVgV6UtJvNy6cWhRg';
		$referer = 'https://plogin.m.jd.com/';

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
		// curl_setopt($ch, CURLOPT_POST, false);
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
            LogModel::log($err, 'getLoginData');

            return $err;
        }

		LogModel::log($res, 'getLoginData');
		return $res;
	}

	/**
	 * [5] 
	 * 提交登入參數
	 */
	public function postLoginData($cookie, $header_arr, $ua, $post_data) {		
		$url     = 'https://plogin.m.jd.com/cgi-bin/mm/dosmslogin';
		$referer = 'https://plogin.m.jd.com/login/login?appid=876&returnurl=https%3A%2F%2Fst.jingxi.com%2Forder%2Fn_detail_v2.shtml%3Fdeal_id%3D' . $this->deal_id . '%26jxsid%3D' . $this->jxsid . '&source=wq_passport';

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
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
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
            LogModel::log($err, 'postLoginData');

            return $err;
        }

		LogModel::log($res, 'postLoginData');
		return $res;
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
