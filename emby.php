<?php

    define('API_KEY', ''); // Your API key
    define('USER', ''); // Your user name
    define('PASS', ''); // Your password

    $baseSystemUrl = ""; // Your base URL
    $baseEmbyUrl = ""; // Your base URL
    $token = post($baseSystemUrl.'/login', array('usuario' => USER, 'senha' => PASS));
    $token = json_decode($token, true);
    $token = $token['token'];
    $listSystemUsers = get($baseSystemUrl.'/clientes', array("Authorization: Bearer ".$token));
    //$listSystemUsers = file_get_contents("clientesSystem.json");
    $listSystemUsers = json_decode($listSystemUsers, true);

    $listSystem = array_map(function($item){
        return array("nome" => sanitizeString($item['nome']),
                    "estado" => $item['estado'],
                    "cpf" => $item['cpf']);
    }, $listSystemUsers);

    $listEmbyUsers = get($baseEmbyUrl.'/Query?api_key='.API_KEY);
    $listEmbyUsers = json_decode($listEmbyUsers, true);

    $listEmby = array_map(function($item){
        return array("nome" => $item['Name'],
                    "estado" => $item['Policy']['IsDisabled'] == 1 ? "bloqueado" : "liberado",
                    "id" => $item['Id']);
    }, $listEmbyUsers['Items']);

    for($i = 0; $i < count($listSystemUsers); $i++){
        if(!in_array(sanitizeString($listSystemUsers[$i]['nome']), array_column($listEmby, 'nome'))){
            $log = "Criando usuário ".sanitizeString($listSystemUsers[$i]['nome'])." com senha ".preg_replace('/[.-]/',"",$listSystemUsers[$i]['cpf'])."\n";
            gravarLog($log);
            $data = array("Name" => sanitizeString($listSystemUsers[$i]['nome']));
            $data = json_encode($data);
            $headers = array("content-type: application/json");
            $response = post($baseEmbyUrl.'/New?api_key='.API_KEY, $data, $headers);
            $response = json_decode($response, true);
            $id = $response['Id'];
            $data = array("CurrentPw"=> "","NewPw"=> preg_replace('/[.-]/',"",$listSystemUsers[$i]['cpf']));
            $data = json_encode($data);
            $respose = post($baseEmbyUrl.'/'.$id.'/Password?api_key='.API_KEY, $data, $headers);
            $data = array(
                "IsAdministrator"=> false,
				"IsHidden"=> true,
				"IsHiddenRemotely"=> true,
				"IsHiddenFromUnusedDevices"=> false,
				"IsDisabled"=> false,
				"MaxParentalRating"=> 9,
				"BlockedTags"=> [],
				"IsTagBlockingModeInclusive"=> false,
				"EnableUserPreferenceAccess"=> false,
				"AccessSchedules"=> [],
				"BlockUnratedItems"=> [],
				"EnableRemoteControlOfOtherUsers"=> false,
				"EnableSharedDeviceControl"=> false,
				"EnableRemoteAccess"=> true,
				"EnableLiveTvManagement"=> false,
				"EnableLiveTvAccess"=> true,
				"EnableMediaPlayback"=> true,
				"EnableAudioPlaybackTranscoding"=> true,
				"EnableVideoPlaybackTranscoding"=> true,
				"EnablePlaybackRemuxing"=> true,
				"EnableContentDeletion"=> false,
				"EnableContentDeletionFromFolders"=> [],
				"EnableContentDownloading"=> false,
				"EnableSubtitleDownloading"=> false,
				"EnableSubtitleManagement"=> false,
				"EnableSyncTranscoding"=> false,
				"EnableMediaConversion"=> true,
				"EnabledChannels"=> [],
				"EnableAllChannels"=> true,
				"EnabledFolders"=> [],
				"EnableAllFolders"=> true,
				"InvalidLoginAttemptCount"=> 0,
				"EnablePublicSharing"=> false,
				"RemoteClientBitrateLimit"=> 0,
				"AuthenticationProviderId"=> "Emby.Server.Implementations.Library.DefaultAuthenticationProvider",
				"ExcludedSubFolders"=> [],
				"SimultaneousStreamLimit"=> 0,
				"EnabledDevices"=> [],
				"EnableAllDevices"=> true
            );
            $data = json_encode($data);
            $respose = post($baseEmbyUrl.'/'.$id.'/Policy?api_key='.API_KEY, $data, $headers);

            gravarLog("Criado com sucesso\n");

        }
        if(in_array(sanitizeString($listSystemUsers[$i]['nome']), array_column($listEmby, 'nome'))){
            $index = array_search(sanitizeString($listSystemUsers[$i]['nome']), array_column($listEmby, 'nome'));
            if($listEmby[$index]['estado'] != $listSystemUsers[$i]['estado']){
                $log = "Alterando estado do cliente ".$listSystemUsers[$i]['nome']." para ".$listSystemUsers[$i]['estado']."\n";
                gravarLog($log);
                $estado = $listSystemUsers[$i]['estado'] == "liberado" ? false : true;
                $headers = array("content-type: application/json");
                $data = array(
                        "IsAdministrator"=> false,
                    	"IsHidden"=> true,
                    	"IsHiddenRemotely"=> true,
                    	"IsHiddenFromUnusedDevices"=> false,
                    	"IsDisabled"=> $estado,
                    	"MaxParentalRating"=> 9,
                    	"BlockedTags"=> [],
                    	"IsTagBlockingModeInclusive"=> false,
                    	"EnableUserPreferenceAccess"=> false,
                    	"AccessSchedules"=> [],
                    	"BlockUnratedItems"=> [],
                    	"EnableRemoteControlOfOtherUsers"=> false,
                    	"EnableSharedDeviceControl"=> false,
                    	"EnableRemoteAccess"=> true,
                    	"EnableLiveTvManagement"=> false,
                    	"EnableLiveTvAccess"=> true,
                    	"EnableMediaPlayback"=> true,
                    	"EnableAudioPlaybackTranscoding"=> true,
                    	"EnableVideoPlaybackTranscoding"=> true,
                    	"EnablePlaybackRemuxing"=> true,
                    	"EnableContentDeletion"=> false,
                    	"EnableContentDeletionFromFolders"=> [],
                    	"EnableContentDownloading"=> false,
                    	"EnableSubtitleDownloading"=> false,
                    	"EnableSubtitleManagement"=> false,
                    	"EnableSyncTranscoding"=> false,
                    	"EnableMediaConversion"=> true,
                    	"EnabledChannels"=> [],
                    	"EnableAllChannels"=> true,
                    	"EnabledFolders"=> [],
                    	"EnableAllFolders"=> true,
                    	"InvalidLoginAttemptCount"=> 0,
                    	"EnablePublicSharing"=> false,
                    	"RemoteClientBitrateLimit"=> 0,
                    	"AuthenticationProviderId"=> "Emby.Server.Implementations.Library.DefaultAuthenticationProvider",
                    	"ExcludedSubFolders"=> [],
                    	"SimultaneousStreamLimit"=> 0,
                    	"EnabledDevices"=> [],
                    	"EnableAllDevices"=> true
                    );
                $data = json_encode($data);

                $response = post($baseEmbyUrl.'/'.$listEmby[$index]['id'].'/Policy?api_key='.API_KEY, $data, $headers);

                gravarLog("Alterado com sucesso\n");
                
            }
        }
    }

    $dif = array_diff(array_column($listEmby, 'nome'), sanitizeString(array_column($listSystemUsers, 'nome')));

    $staticUsers = json_decode(file_get_contents("staticUsersEmby.json"));

    foreach($dif as $item){
        $index = array_search($item, array_column($listEmby, 'nome'));
        if(!in_array($listEmby[$index]['nome'], $staticUsers)){
            $log = "Deletando Cliente ".$listEmby[$index]['nome']." / ID:". $listEmby[$index]['id'] ."\n";
            gravarLog($log);
            $response = delete($baseEmbyUrl.'/'.$listEmby[$index]['id'].'?api_key='.API_KEY);
            gravarLog("Deletado com sucesso\n");
        }
    }

    gravarLog("Fim da Execução\n");

    function get($url, $headers = NULL) {

        $ch   = curl_init($url);        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }


    function post($url, $data, $headers = NULL) {

        $ch   = curl_init($url);        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }


    function delete($url, $headers = NULL) {

        $ch   = curl_init($url);        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "delete");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }

    function sanitizeString($str) {
        $str = preg_replace('/[áàãâä]/ui', 'A', $str);
        $str = preg_replace('/[éèêë]/ui', 'E', $str);
        $str = preg_replace('/[íìîï]/ui', 'I', $str);
        $str = preg_replace('/[óòõôö]/ui', 'O', $str);
        $str = preg_replace('/[úùûü]/ui', 'U', $str);
        $str = preg_replace('/[ç]/ui', 'C', $str);
        // $str = preg_replace('/[,(),;:|!"#$%&/=?~^><ªº-]/', '_', $str);
        //$str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '_', $str); // ideia do Bacco :)
        $str = preg_replace('/\'/', '', $str);
        $str = preg_replace('/[`´]/', '', $str);
        return $str;
    }

    function gravarLog($log){
        $file = fopen("logs", "a+");
        fwrite($file, $log."\n");
        fclose($file);
    }



    






?>