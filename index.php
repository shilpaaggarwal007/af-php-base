<?php
class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
    function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
                 'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if ($objResponse->error){
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-".$e->getMessage();
        }
    }
}

/*
 * Class:HTTPTranslator
 *
 * Processing the translator request.
 */
Class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     *
     * @return string.
     *
     */
    function curlRequest($url, $authHeader) {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml", 'Content-Length: 0'));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        //Set HTTP POST Request.
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }
}

try {
    //Client ID of the application.
    $clientID       = "clientId";
    //Client Secret key of the application.
    $clientSecret = "ClientSecret";
    //OAuth Url.
    $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    //Application Scope Url
    $scopeUrl     = "http://api.microsofttranslator.com";
    //Application grant type
    $grantType    = "client_credentials";

    //Create the AccessTokenAuthentication object.
    $authObj      = new AccessTokenAuthentication();
    //Get the Access token.
    $accessToken  = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
    //Create the authorization Header string.
    $authHeader = "Authorization: Bearer ". $accessToken;


    //Set the Params.
    $inputStr        = "una importante contribución a la rentabilidad de la empresa";
    $fromLanguage   = "es";
    $toLanguage        = "en";
    $user            = 'TestUser';
    $category       = "general";
    $uri             = null;
    $contentType    = "text/plain";
    $maxTranslation = 5;

    //Create the string for passing the values through GET method.
    $params = "from=$fromLanguage".
                "&to=$toLanguage".
                "&maxTranslations=$maxTranslation".
                "&text=".urlencode($inputStr).
                "&user=$user".
                "&uri=$uri".
                "&contentType=$contentType";

    //HTTP getTranslationsMethod URL.
    $getTranslationUrl = "http://api.microsofttranslator.com/V2/Http.svc/GetTranslations?$params";

    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();

    //Call the curlRequest.
    $curlResponse = $translatorObj->curlRequest($getTranslationUrl, $authHeader);
    //Interprets a string of XML into an object.
    $xmlObj = simplexml_load_string($curlResponse);
    $translationObj = $xmlObj->Translations;
    $translationMatchArr = $translationObj->TranslationMatch;
    echo "Get Translation For <b>$inputStr</b>";
    echo "<table border ='2px'>";
    echo "<tr><td><b>Count</b></td><td><b>MatchDegree</b></td>
        <td><b>Rating</b></td><td><b>TranslatedText</b></td></tr>";
    foreach($translationMatchArr as $translationMatch) {
        echo "<tr><td>$translationMatch->Count</td><td>$translationMatch->MatchDegree</td><td>$translationMatch->Rating</td>
            <td>$translationMatch->TranslatedText</td></tr>";
    }
    echo "</table></br>";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}

 
if(isset($_POST['formSubmit']) )
{

}
/*function run()
{
$clientid=$_POST[clientId];
$clientsecret=$_POST[ClientSecret];
$input=$_POST[input];
$con = mysqli_connect('localhost','user','password','database');
if(mysqli_connect_errno())
{
printf("connection failed: %s\n",mysqli_connect_error());
exsit();
}  
else
{
$sql="select * from user where clientid='".$clientId."' and Clientsreat='".$clientSecreat."'";
$result=mysqli_query($con,$sql);
if($result==true)
{
echo "welcome".$clientid.;
}
else
echo "wrong input";
}
}*/
$language=$_POST[lang];
getTokens($language, $scopeUrl, $clientID, $clientSecret, $authUrl);
curlRequest($_SERVER['REQUEST_URI'] $authHeader);
?>
<html>
<head>
<title> Translator App
</title>
</head>
<body>
<h4 align="center">Translator</h4>
<br/>
<br/>
<form action="" method="$_POST">
<table align="center">
<tr><td align="right">clientid </td><td> <input type="text" name="clientId" /></td></tr>
<tr><td align="right">clientsecret </td><td> <input type="password" name="clientSecret" /></td></tr>
<tr><td align="right">client data to translate</td><td><input type="text" name="text" name="input" /></td></tr>
<tr> <td align="center"> <select name="lang"> </td> </tr>
<option value="en">English</option>
<option value="gr">German</option>
<option value="fr">Franch</option>
<option value="hi">Hindi</option>
<option value="po">Polish</option>
<tr> <tdalign="center"> <input type="submit" value="Submit" name="Submit" /> </td> </tr>
</table>
</form>
</body>
</html>
