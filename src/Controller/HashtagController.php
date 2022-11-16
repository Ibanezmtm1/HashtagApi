<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HashtagController extends AbstractController
{
    #[Route('/hashtag/{keyword}', name: 'app_hashtag')]
    public function index(string $keyword): JsonResponse
    {

        $hashtags = $this->getHashtags2($keyword);

        return $this->json([
            'hashtags' => $hashtags,
        ]);
    }

    function getHashtags2(string $keyword)
    {

        $urlParameter = "https://google.com/complete/search?output=toolbar&gl=de&q=";
        $urlParameter .= str_replace(' ', '%20', $keyword);
        $urlParameter .= '%20';

        $keyword = str_replace(' ', '%20', $keyword);
        $url = 'https://www.instagram.com/api/v1/web/search/topsearch/?context=blended&query=%23' . $keyword;//%23 = # //%20 = ' ' empty space

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Accept: application/xml", //cookie von der session nehmen, wenn man den link manuell aufruft
                'cookie: mid=Y3TkSQALAAEdRCInQRBwpr2uGUPC; ig_did=CB8E60E7-9FB5-48CC-B35A-29C1782AC465; shbid="9819\05456649746822\0541700141006:01f7341cf6a57534832b1bd8c628f48dd66d1fd21d4b4f6fd28307565bca85ad6faeae2c"; shbts="1668605006\05456649746822\0541700141006:01f76ea9b4a88046d2083a6d06518b110ff64ae488db999cdf23dff0eb84cd326d7e79c8"; dpr=1.100000023841858; csrftoken=xLM6eJYOwWqdz0tMCpCmYT59gzRwEbnK; ds_user_id=56488259937; sessionid=56488259937%3A1xz93n10gnkqsw%3A13%3AAYfwgyf6ApTnas7EBwH3psC0_PdzUJVovUuDC0qU5g; datr=-el0Y3TwfZeQqrL5PfKKlpeF; rur="LDC\05456488259937\0541700142481:01f70fd4c2ecc207b599d1ff94a276a3a188c6bc36ca1b70cfbe34fc5c09569060e2508e"',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $response = $this->removeEmoji($response);
        $response = utf8_encode($response);
        $response = utf8_encode($response);

        $arr = json_decode($response, true);

        $hashtagCollection = [];

        foreach ($arr['hashtags'] as $value) {
            $hashtagCollection[] = [$value['hashtag']['media_count'], $value['hashtag']['name'], $value['hashtag']['search_result_subtitle']];
        }

        if (array_key_exists('message', $arr)) {
            return 'There was an Error, please try again';
        }
        if (array_key_exists('exeption', $arr)) {
            return 'There was an Error, please try again';
        }

        if ($err) {
            return "cURL Error #:" . $err;
        }
        return $hashtagCollection;

    }

    function removeEmoji(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return iconv('ISO-8859-15', 'UTF-8', $text);
    }
}
