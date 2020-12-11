<?php
namespace Sharoff\Mango\Api\Base;

use Sharoff\Mango\Api\MangoResponse;

Class MangoOfficeError {

    static protected $errors = [
        '1000' => 'Действие успешно выполнено',
        '1100' => 'Вызов завершен в нормальном режиме',
        '1110' => 'Вызов завершен вызывающим абонентом',
        '1111' => 'Вызов не получил ответа в течение времени ожидания',
        '1120' => 'Вызов завершен вызываемым абонентом',
        '1121' => 'Получен ответ "занято" от удаленной стороны',
        '1122' => 'Вызов отклонен вызываемым абонентом',
        '1123' => 'Получен сигнал "не беспокоить"',
        '1130' => 'Ограничения для вызываемого номера',
        '1131' => 'Вызываемый номер недоступен',
        '1132' => 'Вызываемый номер не обслуживается',
        '1133' => 'Вызываемый номер не существует',
        '1134' => 'Превышено максимальное число переадресаций',
        '1140' => 'Вызовы на регион запрещены настройками ВАТС',
        '1150' => 'Ограничения для вызывающего номера',
        '1151' => 'Вызывающий номер в «черном» списке',
        '1152' => 'Вызывающий номер не найден в «белом» списке',
        '1160' => 'Вызов на группу не удался',
        '1161' => 'Удержание запрещено настройками ВАТС',
        '1162' => 'Очередь удержания заполнена',
        '1163' => 'Превышено время ожидания в очереди удержания',
        '1164' => 'Все операторы в данный момент недоступны',
        '1170' => 'Вызов завершен согласно схеме переадресации',
        '1171' => 'Неверно настроена схема переадресации',
        '1180' => 'Вызов завершен командой пользователя',
        '1181' => 'Вызов завершен по команде из внешней системы',
        '1182' => 'Вызов завершен перехватом на другого оператора (только для исходящих плеч)',
        '1183' => 'Назначен новый оператор (при команде ApiConnect. Обычно при переводах)',
        '1190' => 'Вызываемый номер неактивен либо нерабочее расписание',
        '1191' => 'Вызываемый номер неактивен (снят флажок активности ЛК)',
        '1192' => 'Вызываемый номер неактивен по расписанию',
        '2000' => 'Ограничение биллинговой системы',
        '2100' => 'Доступ к счету невозможен',
        '2110' => 'Счет заблокирован',
        '2120' => 'Счет закрыт',
        '2130' => 'Счет не обслуживается (frozen)',
        '2140' => 'Счет недействителен',
        '2200' => 'Доступ к счету ограничен',
        '2210' => 'Доступ ограничен периодом использо`вания',
        '2211' => 'Достигнут дневной лимит использования услуги',
        '2212' => 'Достигнут месячный лимит использования услуги',
        '2220' => 'Количество одновременных вызовов/действий ограничено',
        '2230' => 'Услуга недоступна',
        '2240' => 'Недостаточно средств на счете',
        '2250' => 'Ограничение на количество использований услуги в биллинге',
        '2300' => 'Направление заблокировано',
        '2400' => 'Ошибка биллинга',
        '3000' => 'Неверный запрос',
        '3100' => 'Переданы неверные параметры команды',
        '3101' => 'Запрос выполнен по методу, отличному от POST',
        '3102' => 'Значение ключа не соответствуют расчитанному',
        '3103' => 'В запросе отсутствует обязательный параметр',
        '3104' => 'Параметр передан в неправильном формате',
        '3105' => 'Неверный ключ доступа',
        '3200' => 'Неверно указан номер абонента',
        '3300' => 'Объект не существует',
        '3310' => 'Вызов не найден',
        '3320' => 'Запись разговора не найдена',
        '3330' => 'Номер не найден у ВАТС или сотрудника',
        '4000' => 'Действие не может быть выполнено',
        '4001' => 'Команда не поддерживается',
        '4002' => 'Продолжительность записи меньше минимально возможной в ВАТС, запись не будет сохранена',
        '4100' => 'Выполнить команду по логике работы ВАТС невозможно',
        '4101' => 'Вызов завершен либо не существует',
        '4102' => 'Запись разговора уже осуществляется',
        '4200' => 'Связаться с абонентом в данный момент невозможно',
        '4300' => 'SMS сообщение отправить не удалось',
        '4301' => 'SMS сообщение устарело',
        '5000' => 'Ошибка сервера',
        '5001' => 'Перегрузка',
        '5002' => 'Перезапуск',
        '5003' => 'Технические проблемы',
        '5004' => 'Проблемы доступа к базе данных'
    ];

    static function error($code) {
        if (isset(self::$errors[$code])) {
            MangoResponse::send(
                [
                    'code' => $code
                ],
                420
            );
        }
        return self::error(5000);
    }

}