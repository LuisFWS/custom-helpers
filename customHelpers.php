<?php

if (!function_exists('onlyNumber')) {

    function onlyNumber(?string $param): ?string
    {
        if (empty($param)) {
            return null;
        }
        return preg_replace('/[^0-9]/', '', $param);
    }
}

if (!function_exists('convertStringToDouble')) {

    function convertStringToDouble(?string $value, $default = null, $precision = 2)
    {
        if (empty($value)) {
            return $default;
        }

        if (is_numeric($value)) {
            return round($value, $precision);
        }

        if (str_contains(substr($value, -3), '.')) {
            return round(floatval(str_replace(',', '', $value)), $precision);
        }

        return round(floatval(str_replace(',', '.', str_replace('.', '', $value))), $precision);
    }
}

if (!function_exists('convertStringToDate')) {

    function convertStringToDate(?string $param, $default = null)
    {
        if (empty($param)) {
            return $default;
        }

        $dateTimeArray = explode(' ', $param);
        $time = !empty($dateTimeArray[1]) ? ' ' . $dateTimeArray[1] : '';
        $param = $dateTimeArray[0];

        if (validDateFormat($param, 'Y-m-d')) {
            return $param . $time;
        }

        list($day, $month, $year) = explode('/', $param);
        return (new \DateTime($year . '-' . $month . '-' . $day))->format('Y-m-d') . $time;
    }
}

/**
 * Convert double format to BRL string
 */
if (!function_exists('convertFloatToBRL')) {

    function convertFloatToBRL($value, $withRS = false): string
    {
        return ($withRS ? 'R$ ' : '') . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('validDateFormat')) {

    function validDateFormat(?string $date, $format = 'd/m/Y H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!function_exists('validCpf')) {

    function validCpf($cpf)
    {
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('validDDD')) {

    /**
     * Valid DDD
     *
     * @param $ddd
     * @return bool true is valid
     */
    function validDDD($ddd): bool
    {
        $invalids = ['25', '26', '29', '36', '39', '52', '72', '76', '78'];
        return !($ddd < 11 || $ddd > 99 || substr($ddd, '-1') == 0 || in_array($ddd, $invalids));
    }
}

if (!function_exists('phoneValidate')) {
    /**
     * A função abaixo demonstra o uso de uma expressão regular que identifica, de forma simples, telefones válidos no Brasil.
     * Nenhum DDD iniciado por 0 é aceito, e nenhum número de telefone pode iniciar com 0 ou 1.
     * Exemplos válidos: +55 (11) 98888-8888 / 9999-9999 / 21 98888-8888 / 5511988888888
     *
     * @param $phone
     * @return bool
     */
    function phoneValidate($phone): bool
    {
        $phone = preg_replace('/\D/', '', $phone);// Remove todos os caracteres não numéricos
        $phone = ltrim($phone, '0');// Remove zero on left

        // Verifica se o telefone tem entre 10 e 12 dígitos
        if (strlen($phone) < 10 || strlen($phone) > 12) {
            return false;
        }

        // Extrai o DDD do telefone
        $ddd = substr($phone, 0, 2);

        // Verifica se o DDD é válido
        if (!validDDD($ddd)) {
            return false;
        }

        // Verifica se o telefone tem 8 ou 9 dígitos (sem contar o DDD)
        $phoneWithoutDDD = substr($phone, 2);
        return !(strlen($phoneWithoutDDD) !== 8 && strlen($phoneWithoutDDD) !== 9);
    }
}
if (!function_exists('normalizePhone')) {

    function normalizePhone(?string $phone = null): ?string
    {

        if (empty($phone)) {
            return null;
        }

        // Remover todos os caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Verificar se o telefone tem pelo menos 10 dígitos (2 para DDD + 8 ou 9 para o número)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return null;
        }

        // Extrair o DDD (primeiros 2 dígitos)
        $ddd = substr($phone, 0, 2);

        // Verificar se o DDD é válido (01 a 99)
        if ($ddd < '01' || $ddd > '99') {
            return null;
        }

        // Extrair o número de telefone (restante dos dígitos)
        $number = substr($phone, 2);

        // Verificar se o número tem 8 ou 9 dígitos
        if (strlen($number) < 8 || strlen($number) > 9) {
            return null;
        }

        // Retornar o telefone no formato padrão brasileiro
        return $ddd . $number;
    }
}

/**
 * PHP Máscara CNPJ, CPF, Data e qualquer outra coisa
 * echo mask($cnpj, '##.###.###/####-##')
 * echo mask($cpf, '###.###.###-##')
 * echo mask($cep, '#####-###')
 * echo mask($data, '##/##/####')
 * echo mask($data, '[##][##][####]')
 * echo mask($data, '(##)(##)(####)')
 * echo mask($hora, 'Agora são ## horas ## minutos e ## segundos')
 * echo mask($hora, '##:##:##');
 */
if (!function_exists('mask')) {

    function mask($val, $mask): string
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }
}

if (!function_exists('phoneMask')) {

    function phoneMask($val): string
    {
        if (empty($val = onlyNumber($val))) {
            return '';
        }

        if (strlen($val) === 11) {
            return mask($val, '(##) #####-####');
        }
        return mask($val, '(##) ####-####');
    }
}

if (!function_exists('documentMask')) {

    function documentMask($val): array|string|null
    {
        if (empty($val = onlyNumber($val))) {
            return '';
        }

        //CPF
        if (strlen($val) === 11) {
            return mask($val, '###.###.###-##');
        }

        //CNPJ
        if (strlen($val) === 14) {
            return mask($val, '##.###.###/####-##');
        }

        return $val;
    }
}

if (!function_exists('addPercent')) {

    function addPercent($value, $percent, $default = null, $precision = 2)
    {
        if (empty($value)) {
            return $default;
        }
        $totalPercent = ($percent / 100) * $value;
        return round($value + $totalPercent, $precision);
    }
}
