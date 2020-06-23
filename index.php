<?php
require __DIR__ . '/vendor/autoload.php';

$client = Symfony\Component\HttpClient\HttpClient::create(['verify_peer' => false]);

define('CEI_URL_BASE', 'https://cei.b3.com.br/CEI_Responsivo/');
define('CEI_URL_LOGIN', CEI_URL_BASE . 'login.aspx');
define('CEI_URL_DIVIDENDS', CEI_URL_BASE . 'ConsultarProventos.aspx');

define('CEI_CPF', 'seucpf');
define('CEI_SENHA', 'suasenha');
define('EMAIL', 'seuemail@gmail.com');
define('SENHA', 'suasenha');

$browser = new Symfony\Component\BrowserKit\HttpBrowser($client);

try {
    $loginScreen = $browser->request('GET', CEI_URL_LOGIN);
    $loginForm = $loginScreen->selectButton('Entrar')->form([
        'ctl00$ContentPlaceHolder1$txtLogin' => CEI_CPF,
        'ctl00$ContentPlaceHolder1$txtSenha' => CEI_SENHA
    ]);
    $browser->submit($loginForm);
} catch (\Exception $e) {
    die($e->getMessage());
}
$dividendsResult;
try {
    $dividendsScreen = $browser->request('GET', CEI_URL_DIVIDENDS);
    $dividendsForm = $dividendsScreen->selectButton('Consultar')->form();
    $dividendsResult = $browser->submit($dividendsForm);
} catch (\Exception $e) {
    die($e->getMessage());
}
$dividends = $dividendsResult->filter('tbody tr')->each(function ($tr) {
    return [
        'payer' => $tr->filter('td')->eq(0)->text(),
        'date' => preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '$3-$2-$1', $tr->filter('td')->eq(3)->text()),
        'ammount' => floatval(preg_replace('/,/', '.', $tr->filter('td')->eq(7)->text()))
    ];
});

$now = date('Y-m-d');
$payers = [];
$total = 0;
foreach ($dividends as $dividend) {
    if (date('Y-m-d', strtotime($dividend['date'])) == $now) {
        $total += $dividend['ammount'];
        $payers[] = $dividend['payer'];
    }
}

if ($total == 0) {
    die('no money for you today :/');
}

$totalBrl = number_format($total, 2, ',', '.');
$subject = 'R$ ' . $totalBrl . ' a mais na conta!';
$body = 'Vou receber no total R$ ' . $totalBrl . ' dos ativo(s): ' . implode(', ', $payers);

$email = (new Symfony\Component\Mime\Email())
    ->from(EMAIL)
    ->to(EMAIL)
    ->subject($subject)
    ->text($body);

$transport = new Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport(EMAIL, SENHA);
$mailer = new Symfony\Component\Mailer\Mailer($transport);
$mailer->send($email);