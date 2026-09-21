(function () {
    'use strict';

    var D = {

        'Главная': 'Home',
        'Магазин': 'Shop',
        'Правила': 'Rules',
        'Соглашение': 'Terms',
        'Поддержка': 'Support',
        'Профиль': 'Profile',
        'Личный кабинет': 'Account',
        'Войти': 'Sign in',
        'Вход': 'Sign in',
        'Вход —': 'Sign in —',
        'Регистрация': 'Sign up',
        'Регистрация —': 'Sign up —',
        'Выйти': 'Log out',
        'Меню': 'Menu',
        'В сети': 'Online',
        'готов к запуску': 'ready to launch',
        'В сети · готов к запуску': 'Online · ready to launch',

        'Новая версия уже доступна': 'A new version is already available',
        'Клиент, который': 'A client that',
        'просто работает': 'just works',
        'и делает своё дело': 'and does the job',
        'Стабильный запуск, понятный интерфейс и своевременные обновления —': 'Stable launch, clean interface and timely updates —',
        'без лишних слов и обещаний': 'no empty promises',
        'Всё, что нужно для комфортной игры.': 'Everything you need for a comfortable game.',
        'запусков': 'launches',
        'клиентов': 'clients',
        'клиенты': 'clients',
        'версий': 'versions',
        'Launcher': 'Launcher',
        'Играть': 'Play',
        'Обновление файлов': 'Updating files',
        'Пара кликов — и вы в игре. Клиент стартует быстро даже на не самом мощном железе.': 'A couple of clicks — and you are in the game. The client starts fast even on not-so-powerful hardware.',
        'Быстрый запуск': 'Fast launch',
        'Автообновления': 'Auto updates',

        'Возможности': 'Features',
        'Всё необходимое': 'Everything you need',
        'для': 'for',
        'комфортной игры': 'a comfortable game',
        'Ничего лишнего — только то, что действительно влияет на игровой опыт.': 'Nothing extra — only what really matters for your gaming experience.',
        'Надёжная защита': 'Reliable protection',
        'Аккаунт и HWID под защитой: продуманная система проверок без лишнего дискомфорта.': 'Account and HWID protected: a well-designed verification system without extra hassle.',
        'Своевременные обновления': 'Timely updates',
        'Вышло обновление — клиент готов. Без долгих ожиданий и переносов сроков.': 'Update released — the client is ready. No long waits or delays.',
        'Множество версий': 'Many versions',
        'От классических до самых свежих. Переключение между версиями — в один клик.': 'From classic to the newest. Switch between versions in one click.',
        'Настройка под себя': 'Customize it',
        'Гибкие параметры и пресеты: настройте клиент под свой стиль и сохраните конфиг.': 'Flexible settings and presets: tune the client to your style and save the config.',
        'Поддержка на связи': 'Support is online',
        'Вопросы — в Discord или Telegram. Отвечают живые люди, без ботов и шаблонов.': 'Questions — in Discord or Telegram. Real people answer, no bots or templates.',

        'Отзывы': 'Reviews',
        'Что говорят': 'What our',
        'наши': 'our',
        'Реальные отзывы тех, кто уже играет вместе с нами.': 'Real reviews from those already playing with us.',
        'Куплено': 'Purchased',
        'Отзывов пока нет — станьте первым!': 'No reviews yet — be the first!',

        'Обзор': 'Overview',
        'Посмотрите': 'See for',
        'сами': 'yourself',
        'Короткое видео о клиенте — интерфейс и возможности за пару минут.': 'A short video about the client — interface and features in a couple of minutes.',
        'Обзор клиента': 'Client overview',

        'Готовы попробовать?': 'Ready to try it out?',
        'Создайте аккаунт и начните играть уже сегодня': 'Create an account and start playing today',
        'В магазин': 'Go to shop',

        'Платформа': 'Platform',
        'Разделы': 'Sections',
        'Мы на связи': 'Contact us',
        '— все права защищены': '— All rights reserved',
        'все права защищены': 'All rights reserved',
        'Не связан с Mojang и Microsoft': 'Not affiliated with Mojang or Microsoft',
        'Игровой клиент для Minecraft. Стабильно, понятно и без лишнего.': 'A Minecraft client. Stable, clear and without extra stuff.',
        'Discord': 'Discord',
        'Telegram': 'Telegram',
        'YouTube': 'YouTube',

        'Создать': 'Create',
        'Найти': 'Search',
        'Сбросить': 'Reset',
        'Удалить': 'Delete',
        'Отмена': 'Cancel',
        'Сохранить': 'Save',
        'Отправить': 'Send',
        'Закрыть': 'Close',
        'Назад': 'Back',
        'Заполните все поля': 'Fill in all fields',
        'Неверный CSRF-токен': 'Invalid CSRF token',
        'Ошибка сети:': 'Network error:',

        'Добро пожаловать': 'Welcome',
        'Войдите в свой аккаунт': 'Sign in to your account',
        'Логин или Email': 'Login or Email',
        'Логин': 'Login',
        'Email': 'Email',
        'Пароль': 'Password',
        'Подтверждение': 'Confirmation',
        'Повторите пароль': 'Repeat password',
        'Введите логин или email': 'Enter login or email',
        'Введите пароль': 'Enter password',
        'Введите логин': 'Enter login',
        'Введите email': 'Enter email',
        'Минимум 6 символов': 'Minimum 6 characters',
        'Нет аккаунта?': 'No account?',
        'Уже есть аккаунт?': 'Already have an account?',
        'Создать аккаунт': 'Create account',
        'Зарегистрироваться': 'Sign up',
        'Присоединяйся к': 'Join',
        'Слишком много неудачных попыток. Попробуйте снова через': 'Too many failed attempts. Try again in',
        'минут.': 'minutes.',
        'минут(ы)': 'minute(s)',
        'Подтвердите что вы не робот': 'Confirm you are not a robot',
        'Проверка не пройдена. Попробуйте снова.': 'Verification failed. Try again.',
        'Неверный логин или пароль': 'Invalid login or password',
        'Нарушение правил': 'Rules violation',
        'HWID в черном списке': 'HWID is blacklisted',
        'IP в черном списке': 'IP is blacklisted',
        'Пожалуйста, подтвердите что вы не робот': 'Please confirm you are not a robot',
        'Аккаунт заблокирован': 'Account blocked',
        'Причина:': 'Reason:',
        'По всем вопросам': 'For any questions',
        'обращайтесь в поддержку': 'contact support',
        'Логин должен быть от 3 до 20 символов': 'Login must be 3 to 20 characters',
        'Логин может содержать только латиницу, цифры и _': 'Login may contain only Latin letters, digits and _',
        'Некорректный email': 'Invalid email',
        'Пароль должен быть не менее 6 символов': 'Password must be at least 6 characters',
        'Пароли не совпадают': 'Passwords do not match',
        'Пользователь с таким логином или email уже существует': 'A user with this login or email already exists',

        'Профиль —': 'Profile —',
        'Добро пожаловать,': 'Welcome,',
        'Все данные, подписка и настройки аккаунта': 'All your data, subscription and account settings',
        '— в одном месте': '— in one place',
        'Выбрать аватарку': 'Choose avatar',
        'У вас пока нет сохранённых аватарок': 'You have no saved avatars',
        'Загрузить новую': 'Upload new',
        'Подписка': 'Subscription',
        'Активна': 'Active',
        'Неактивна': 'Inactive',
        'до': 'until',
        'Активация ключа': 'Key activation',
        'Введите лицензионный ключ': 'Enter license key',
        'Активировать': 'Activate',
        'Ключ продлевает подписку сверх текущей': 'The key extends your subscription beyond the current one',
        'Ключ активирован! Подписка продлена на': 'Key activated! Subscription extended for',
        'дней': 'days',
        'дня': 'days',
        'Информация об аккаунте': 'Account info',
        'Привязан': 'Linked',
        'Не привязан': 'Not linked',
        'отсутствует': 'missing',
        'Последний вход': 'Last login',
        'Лаунчер': 'Launcher',
        'ОС': 'OS',
        'Процессор': 'CPU',
        'Память': 'Memory',
        'Версия:': 'Version:',
        'Скачать лаунчер': 'Download launcher',
        'Не скачалось?': 'Did not download?',
        'Лаунчер на технических работах — скачивание временно недоступно': 'Launcher is under maintenance — download temporarily unavailable',
        'Скачивание лаунчера доступно только с активной подпиской': 'Launcher download is available only with an active subscription',
        'Нужна помощь?': 'Need help?',
        'Остались вопросы по подписке, лаунчеру или аккаунту? Напишите нам — поможем.': 'Questions about subscription, launcher or account? Write to us — we will help.',
        'Открыть поддержку': 'Open support',
        'Безопасность': 'Security',
        'Смена пароля': 'Change password',
        'Текущий пароль': 'Current password',
        'Новый пароль (мин. 6)': 'New password (min. 6)',
        'Повторите новый пароль': 'Repeat new password',
        'Сменить пароль': 'Change password',
        'Ваш отзыв': 'Your review',
        'Ваш отзыв проходит модерацию': 'Your review is under moderation',
        'Ваш отзыв опубликован': 'Your review is published',
        'Отзыв отклонён — отправьте новый': 'Review rejected — submit a new one',
        'Поделитесь своим опытом...': 'Share your experience...',
        'Отправить повторно': 'Send again',
        'Оставить отзыв': 'Leave a review',
        'Отзывы проходят модерацию перед публикацией': 'Reviews are moderated before publishing',
        'Отзывы могут оставлять только пользователи с активной подпиской': 'Only users with an active subscription can leave reviews',
        'Общий чат': 'General chat',
        'Онлайн:': 'Online:',
        'Сообщения хранятся 1 час': 'Messages are stored for 1 hour',
        'Пока нет сообщений — напишите первым!': 'No messages yet — write the first!',
        'с лаунчера': 'from launcher',
        'с сайта': 'from website',
        'Напишите сообщение...': 'Type a message...',
        'Чат доступен только для премиум-пользователей': 'Chat is available only for premium users',
        'Чат доступен только для пользователей с активной подпиской': 'Chat is available only for users with an active subscription',
        'Вы забанены в чате до': 'You are banned in chat until',
        'Пожалуйста, подождите 4 секунды перед отправкой следующего сообщения': 'Please wait 4 seconds before sending the next message',
        'Сообщение должно содержать минимум 2 символа': 'The message must contain at least 2 characters',
        'Сообщение не должно превышать 500 символов': 'The message must not exceed 500 characters',
        'Вы забанены в чате на 1 час за спам': 'You are banned in chat for 1 hour for spam',
        'Отзыв должен содержать минимум 5 символов': 'The review must contain at least 5 characters',
        'Ваш отзыв уже отправлен на модерацию. Ожидайте проверки.': 'Your review has been sent for moderation. Wait for review.',
        'Ваш отзыв уже опубликован и одобрен.': 'Your review is already published and approved.',
        'Ваш отзыв отправлен на повторную модерацию!': 'Your review has been sent for re-moderation!',
        'Спасибо за ваш отзыв! Он отправлен на модерацию.': 'Thank you for your review! It has been sent for moderation.',
        'Текущий пароль неверный': 'Current password is incorrect',
        'Новый пароль должен быть не менее 6 символов': 'New password must be at least 6 characters',
        'Пароль успешно изменён': 'Password changed successfully',
        'Недействительный ключ': 'Invalid key',
        'Файл не должен превышать 2MB': 'File must not exceed 2MB',
        'Файл не является изображением': 'File is not an image',
        'Разрешены только JPG, PNG, GIF, WEBP': 'Only JPG, PNG, GIF, WEBP are allowed',
        'Аватарка успешно загружена': 'Avatar uploaded successfully',
        'Ошибка при загрузке файла': 'Error uploading file',
        'Выберите файл для загрузки': 'Choose a file to upload',
        'Аватарка изменена': 'Avatar changed',
        'Пользователь': 'User',
        'Администратор': 'Administrator',
        '+30д': '+30d',
        '+90д': '+90d',
        '+365д': '+365d',
        'Отзыв': 'Review',

        'Магазин —': 'Shop —',
        'Выбери свой тариф': 'Choose your plan',
        'Выбери свой': 'Choose your',
        'тариф': 'plan',
        'Получи доступ ко всем возможностям': 'Get access to all features',
        'Популярный': 'Popular',
        '1 месяц': '1 month',
        'Полный доступ к клиенту': 'Full access to the client',
        'Все функции и модули': 'All features and modules',
        'Поддержка 24/7': '24/7 support',
        'Регулярные обновления': 'Regular updates',
        'Где купить': 'Where to buy',
        'Автоматическая активация после оплаты': 'Automatic activation after payment',
        'Недоступно': 'Unavailable',
        '1 год': '1 year',
        'Приоритетная поддержка': 'Priority support',
        'Ранний доступ к бета-версиям': 'Early access to beta versions',
        'Временно недоступно': 'Temporarily unavailable',
        'Лучший выбор': 'Best choice',
        'Навсегда': 'Forever',
        'Бессрочный доступ': 'Lifetime access',
        'VIP поддержка': 'VIP support',
        'Эксклюзивный контент': 'Exclusive content',
        'Промокод просрочен': 'Promo code expired',
        'Промокод больше не работает': 'Promo code no longer works',
        'Промокод активирован! Скидка': 'Promo code activated! Discount',
        'Неверный промокод': 'Invalid promo code',
        'Промокод удалён': 'Promo code deleted',

        'Правила —': 'Rules —',
        'Правила использования': 'Terms of use',
        'использования': 'of use',
        'Ознакомьтесь с основными правилами использования нашего сервиса': 'Read the basic rules of using our service',
        'Раздел 1': 'Section 1',
        'Раздел 2': 'Section 2',
        'Раздел 3': 'Section 3',
        'Раздел 4': 'Section 4',
        'Раздел 5': 'Section 5',
        'Общие положения': 'General provisions',
        'Запрещенные действия': 'Prohibited actions',
        'Ответственность': 'Liability',
        'Конфиденциальность': 'Confidentiality',
        'Изменение правил': 'Changes to rules',
        'Используя': 'By using',
        ', вы автоматически соглашаетесь с данными правилами. Нарушение может привести к блокировке аккаунта без предупреждения.': ', you automatically agree to these rules. A violation may result in account blocking without notice.',
        'Распространение клиента без разрешения администрации': 'Distributing the client without permission of the administration',
        'Продажа аккаунтов и ключей': 'Selling accounts and keys',
        'Использование читов на приватных серверах без согласия владельца': 'Using cheats on private servers without the owner consent',
        'Оскорбление других пользователей и администрации': 'Offending other users and the administration',
        'Попытка взлома или обхода системы лицензирования': 'Attempting to hack or bypass the licensing system',
        'Создание нескольких аккаунтов для получения преимущества': 'Creating multiple accounts to gain an advantage',
        'Администрация не несет ответственности за последствия использования клиента на сторонних серверах. Вы используете продукт на свой страх и риск. Мы не гарантируем, что клиент будет работать на всех серверах без исключения.': 'The administration is not responsible for the consequences of using the client on third-party servers. You use the product at your own risk. We do not guarantee the client will work on all servers.',
        'Мы не передаем ваши личные данные третьим лицам. Вся информация, которую вы предоставляете при регистрации, используется исключительно для работы сервиса и не разглашается.': 'We do not share your personal data with third parties. All information you provide during registration is used solely for the service and is not disclosed.',
        'Администрация оставляет за собой право изменять данные правила в любое время без предварительного уведомления. Актуальная версия всегда доступна на этой странице.': 'The administration reserves the right to change these rules at any time without prior notice. The current version is always available on this page.',
        'По всем вопросам обращайтесь в нашу поддержку — мы всегда на связи.': 'For any questions contact our support — we are always online.',

        'Соглашение —': 'Terms —',
        'Пользовательское соглашение': 'User agreement',
        'Пользовательское': 'User',
        'Условия использования сервиса': 'Terms of service',
        'Обновлено:': 'Updated:',
        'Сбор данных': 'Data collection',
        'Мы собираем минимально необходимую информацию для работы сервиса: имя пользователя, email, HWID для привязки лицензии к устройству, IP-адрес для обеспечения безопасности и предотвращения мошенничества.': 'We collect the minimum information needed for the service: username, email, HWID to bind the license to your device, and IP address for security and fraud prevention.',
        'Хранение данных': 'Data storage',
        'Все пароли шифруются с использованием современных алгоритмов хеширования. Мы не храним пароли в открытом виде. Ваши данные находятся на защищённых серверах с ограниченным доступом.': 'All passwords are encrypted with modern hashing algorithms. We do not store passwords in plain text. Your data is on protected servers with restricted access.',
        'Пароли хранятся в зашифрованном виде': 'Passwords are stored encrypted',
        'Доступ к данным имеют только авторизованные сотрудники': 'Only authorized staff have access to data',
        'Регулярное резервное копирование': 'Regular backups',
        'Третьи стороны': 'Third parties',
        'Мы не передаём, не продаём и не раскрываем ваши персональные данные третьим лицам. Вся собранная информация используется исключительно для работы сервиса и улучшения качества обслуживания.': 'We do not transfer, sell or disclose your personal data to third parties. All collected information is used solely for the service and to improve quality.',
        'Ваши права': 'Your rights',
        'Вы имеете право запросить удаление вашего аккаунта и всех связанных с ним данных. Для этого необходимо обратиться в службу поддержки. После удаления аккаунта восстановление невозможно.': 'You have the right to request deletion of your account and all related data. To do so, contact support. Account recovery is impossible after deletion.',
        'Право на доступ к своим данным': 'Right to access your data',
        'Право на исправление неточных данных': 'Right to correct inaccurate data',
        'Право на удаление аккаунта': 'Right to delete the account',
        'Право на отзыв согласия': 'Right to withdraw consent',
        'Изменения в политике': 'Policy changes',
        'Мы можем обновлять данное соглашение время от времени. О всех существенных изменениях мы уведомим пользователей через сайт или email. Продолжение использования сервиса после изменений означает ваше согласие с новой версией.': 'We may update this agreement from time to time. We will notify users of significant changes via the site or email. Continued use after changes means you accept the new version.',
        'Остались вопросы?': 'Any questions?',
        ', вы соглашаетесь с условиями данного пользовательского соглашения. Если у вас есть вопросы — свяжитесь с нами.': ', you agree to the terms of this user agreement. If you have questions — contact us.',

        'Поддержка —': 'Support —',
        'Служба поддержки': 'Support service',
        'Служба': 'Support',
        'поддержки': 'service',
        'Мы ответим вам в течение 24 часов': 'We will reply within 24 hours',
        'Мои обращения': 'My tickets',
        'Админ-панель': 'Admin panel',
        'Всего': 'Total',
        'Открытых': 'Open',
        'В работе': 'In progress',
        'Закрытых': 'Closed',
        'Создать обращение': 'Create ticket',
        'У вас пока нет обращений': 'You have no tickets yet',
        'Открыт': 'Open',
        'Закрыт': 'Closed',
        'Удалить этот тикет? Все сообщения будут удалены безвозвратно.': 'Delete this ticket? All messages will be permanently deleted.',
        'Новое обращение': 'New ticket',
        'Тема обращения': 'Ticket subject',
        'Например: Проблема с активацией': 'E.g. Activation issue',
        'Сообщение': 'Message',
        'Опишите вашу проблему подробнее...': 'Describe your problem in detail...',
        'Обращение #': 'Ticket #',
        'Изменить статус': 'Change status',
        'Удалить тикет': 'Delete ticket',
        'Введите ваше сообщение... (Ctrl+Enter для отправки)': 'Type your message... (Ctrl+Enter to send)',
        'Этот тикет закрыт. Нельзя оставлять новые сообщения.': 'This ticket is closed. No new messages allowed.',
        'Назад к списку': 'Back to list',
        'Отправка...': 'Sending...',
        'Ошибка при отправке': 'Error sending',
        'Ваш аккаунт заблокирован за спам тикетами. Осталось': 'Your account is blocked for ticket spam. Remaining',
        'Вы создали слишком много обращений (3 за 5 минут). Ваш аккаунт заблокирован на 10 минут.': 'You have created too many tickets (3 per 5 minutes). Your account is blocked for 10 minutes.',
        'С вашего IP слишком много обращений. Аккаунт заблокирован на 30 минут.': 'Too many tickets from your IP. The account is blocked for 30 minutes.',
        'Спам тикетами (10 минут)': 'Ticket spam (10 minutes)',
        'Спам тикетами с одного IP': 'Ticket spam from one IP',
        'Тикет удалён': 'Ticket deleted',
        'Обращение создано! Ожидайте ответа.': 'Ticket created! Waiting for a reply.',
        'Тикет не найден': 'Ticket not found',
        'Введите сообщение': 'Enter a message',
        'Новый ответ в поддержке': 'New reply in support',
        'Администратор ответил на ваше обращение': 'Administrator replied to your ticket',
        'Ответ отправлен!': 'Reply sent!',
        'Статус изменён': 'Status changed',

        'Доступ запрещён. Только для администраторов.': 'Access denied. Administrators only.',
        'Укажите IP-адрес или подсеть': 'Enter an IP address or subnet',
        'Некорректный IP или подсеть': 'Invalid IP or subnet',
        'Добавлен вручную': 'Added manually',
        'IP добавлен в чёрный список': 'IP added to blacklist',
        'Запись удалена из чёрного списка': 'Entry removed from blacklist',
        'Настройки сохранены': 'Settings saved',
        'Логи визитов очищены': 'Visit logs cleared',
        'Логи угроз очищены': 'Threat logs cleared',
        'Фаервол, трекинг посетителей и защита от атак в реальном времени': 'Firewall, visitor tracking and real-time attack protection',
        'Дашборд': 'Dashboard',
        'Посетители': 'Visitors',
        'Угрозы': 'Threats',
        'Чёрный список': 'Blacklist',
        'визитов сегодня': 'visits today',
        'уникальных IP сегодня': 'unique IPs today',
        'запросов за 24 часа': 'requests in 24h',
        'атак заблокировано всего': 'attacks blocked total',
        'атак сегодня': 'attacks today',
        'IP в чёрном списке': 'IPs in blacklist',
        'Визиты за последние 14 дней': 'Visits in the last 14 days',
        'Топ страниц (24ч)': 'Top pages (24h)',
        'Топ стран (7 дней)': 'Top countries (7 days)',
        'Топ User-Agent (24ч)': 'Top user agents (24h)',
        'Пока нет данных': 'No data yet',
        'Поиск по IP или странице...': 'Search by IP or page...',
        'Очистить все логи визитов?': 'Clear all visit logs?',
        'Очистить': 'Clear',
        'Время': 'Time',
        'Страна': 'Country',
        'Метод': 'Method',
        'Страница': 'Page',
        'Реферер': 'Referrer',
        'Визиты не найдены': 'No visits found',
        'Атак не зафиксировано — всё чисто': 'No attacks detected — all clear',
        'всего попыток': 'total attempts',
        'Очистить все логи угроз?': 'Clear all threat logs?',
        'Тип': 'Type',
        'Угроз не обнаружено': 'No threats detected',
        'Заблокировать IP / подсеть': 'Block IP / subnet',
        'IP-адрес или CIDR-подсеть': 'IP address or CIDR subnet',
        'Например: 192.168.1.5 или 45.132.0.0/24': 'E.g. 192.168.1.5 or 45.132.0.0/24',
        'Поддерживаются одиночные IP и подсети CIDR': 'Single IPs and CIDR subnets are supported',
        'Причина': 'Reason',
        'Например: DDoS, спам, атаки': 'E.g. DDoS, spam, attacks',
        'Длительность': 'Duration',
        '0 = навсегда': '0 = forever',
        'Заблокировать': 'Block',
        'IP / Подсеть': 'IP / Subnet',
        'Кем добавлен': 'Added by',
        'Действует до': 'Valid until',
        'Добавлен': 'Added',
        'Чёрный список пуст': 'Blacklist is empty',
        'истёк': 'expired',
        'навсегда': 'forever',
        'Разблокировать?': 'Unblock?',
        'Настройки фаервола': 'Firewall settings',
        'Защита включена': 'Protection enabled',
        'Выключив, сайт перестанет блокировать атаки и считать визиты': 'If disabled, the site stops blocking attacks and counting visits',
        'Блокировать ботов и сканеры': 'Block bots and scanners',
        'sqlmap, nikto, curl, wget, python-requests и другие': 'sqlmap, nikto, curl, wget, python-requests and others',
        'Лимит запросов на IP': 'Requests limit per IP',
        'за окно (ниже)': 'per window (below)',
        'Глобальный лимит (все IP)': 'Global limit (all IPs)',
        '0 = выключен. Блокирует поток запросов даже с разных IP': '0 = off. Blocks request flow even from different IPs',
        'Вес одного захода': 'Weight of one visit',
        '1 заход = N запросов для лимитов (по умолчанию 2)': '1 visit = N requests for limits (default 2)',
        'Бан IP при атаке с разных IP, минут': 'Ban IP on attack from different IPs, minutes',
        'при превышении глобального лимита виновники уходят в чёрный список': 'when the global limit is exceeded, offenders go to the blacklist',
        'Окно rate limit, секунд': 'Rate limit window, seconds',
        'Порог угроз для автобана': 'Threat threshold for auto-ban',
        'сколько атак за 10 минут, чтобы IP попал в чёрный список': 'how many attacks in 10 minutes to blacklist an IP',
        'Автобан, минут': 'Auto-ban, minutes',
        'Хранение визитов, дней': 'Visit storage, days',
        'Защищено': 'Protected',
        'Защищено AstraDLC — фаервол активен: трекинг, блокировка атак, rate limit': 'Protected by AntiPackageLeak — firewall active: tracking, attack blocking, rate limit',
        'AstraDLC — фаервол активен: трекинг, блокировка атак, rate': 'by AntiPackageLeak — firewall active: tracking, attack blocking, rate',

        'Админ панель —': 'Admin panel —',
        'Админ': 'Admin',
        'панель': 'panel',
        'Управление пользователями, ключами, промокодами и отзывами': 'Manage users, keys, promo codes and reviews',
        'Пользователи': 'Users',
        'Ключи': 'Keys',
        'Промокоды': 'Promo codes',
        'Логи': 'Logs',
        'Список пользователей': 'User list',
        'Поиск по ID или username...': 'Search by ID or username...',
        'Роль': 'Role',
        'Действия': 'Actions',
        'Нет пользователей': 'No users',
        'Изменить роль пользователя?': 'Change user role?',
        'Нет': 'No',
        'Выдать подписку на 30 дней': 'Grant subscription for 30 days',
        'Выдать подписку на 90 дней': 'Grant subscription for 90 days',
        'Выдать подписку на 365 дней': 'Grant subscription for 365 days',
        'Выдать подписку навсегда': 'Grant lifetime subscription',
        'Снять подписку': 'Remove subscription',
        'Снять подписку?': 'Remove subscription?',
        'Сбросить HWID': 'Reset HWID',
        'Сбросить HWID?': 'Reset HWID?',
        'Разбан': 'Unban',
        'Разбанить пользователя?': 'Unban user?',
        'Бан': 'Ban',
        'Изменить пароль?': 'Change password?',
        'Введите новый пароль': 'Enter new password',
        'Премиум': 'Premium',
        'Генерация ключей': 'Key generation',
        'Дней': 'Days',
        'Количество': 'Quantity',
        'Создать ключи': 'Create keys',
        'Список ключей': 'Key list',
        'Ключ': 'Key',
        'Код': 'Code',
        'Статус': 'Status',
        'Кем использован': 'Used by',
        'Нет ключей': 'No keys',
        'Использован': 'Used',
        'Активен': 'Active',
        'Удалить ключ?': 'Delete key?',
        'Создать промокод': 'Create promo code',
        'Код (авто если пусто)': 'Code (auto if empty)',
        'Проценты (%)': 'Percent (%)',
        'Фикс (₽)': 'Fixed (₽)',
        'Скидка': 'Discount',
        'Мин. покупка': 'Min. purchase',
        'Макс. использований': 'Max uses',
        '0 (безлимит)': '0 (unlimited)',
        'Список промокодов': 'Promo code list',
        'Использований': 'Uses',
        'Бессрочно': 'Indefinite',
        'Выкл': 'Off',
        'Истёк': 'Expired',
        'Лимит': 'Limit',
        'Удалить промокод?': 'Delete promo code?',
        'Управление отзывами': 'Review management',
        'На модерации (': 'Pending (',
        'Одобренные (': 'Approved (',
        'Отклоненные (': 'Rejected (',
        'Рейтинг': 'Rating',
        'Дата': 'Date',
        'Модератор': 'Moderator',
        'Нет отзывов': 'No reviews',
        'Удален': 'Deleted',
        'Одобрить этот отзыв?': 'Approve this review?',
        'Отклонить этот отзыв?': 'Reject this review?',
        'Удалить этот отзыв?': 'Delete this review?',
        'Журнал действий': 'Action log',
        'Действие': 'Action',
        'Цель': 'Target',
        'Детали': 'Details',
        'Нет записей': 'No records',
        'Создал ключи': 'Created keys',
        'Выдал подписку': 'Granted subscription',
        'Снял подписку': 'Removed subscription',
        'Забанил': 'Banned',
        'Разбанил': 'Unbanned',
        'Сбросил HWID': 'Reset HWID',
        'Сменил пароль': 'Changed password',
        'Сменил роль': 'Changed role',
        'Удалил ключ': 'Deleted key',
        'Создал промокод': 'Created promo code',
        'Удалил промокод': 'Deleted promo code',
        'Переключил промокод': 'Toggled promo code',
        'Одобрил отзыв': 'Approved review',
        'Отклонил отзыв': 'Rejected review',
        'Удалил отзыв': 'Deleted review',
        'Отредактировал отзыв': 'Edited review',
        'Блокировка': 'Blocking',
        'Пользователь:': 'User:',
        'Причина блокировки': 'Block reason',
        'Бан по HWID': 'HWID ban',
        'Забанить': 'Ban',
        'Редактировать отзыв': 'Edit review',
        'Текст отзыва': 'Review text',
        'Отзыв одобрен': 'Review approved',
        'Отзыв отклонен': 'Review rejected',
        'Отзыв удален': 'Review deleted',
        'Отзыв отредактирован': 'Review edited',
        'Создано': 'Created',
        'ключей на': 'keys for',
        'Пользователю #': 'User #',
        'выдана подписка на': 'granted subscription for',
        'НАВСЕГДА': 'FOREVER',
        'Подписка пользователя #': 'Subscription of user #',
        'снята': 'removed',
        'Пользователь #': 'User #',
        'ЗАБАНЕН. Причина:': 'BANNED. Reason:',
        'Пользователь был выкинут из аккаунта.': 'User was logged out.',
        'HWID также заблокирован.': 'HWID is also blocked.',
        'Пользователь не найден': 'User not found',
        'РАЗБАНЕН': 'UNBANNED',
        'пользователя #': 'of user #',
        'сброшен': 'reset',
        'Пароль пользователя #': 'Password of user #',
        'изменён': 'changed',
        'Роль пользователя #': 'Role of user #',
        'изменена на': 'changed to',
        'Промокод': 'Promo code',
        'уже существует': 'already exists',
        'создан': 'created',
        'Вы не можете заблокировать самого себя!': 'You cannot block yourself!',

        'Управление лаунчером —': 'Launcher management —',
        'Управление': 'Management',
        'лаунчером': 'launcher',
        'Настройка лаунчера, версий, новостей и просмотр статистики': 'Configure the launcher, versions, news and view statistics',
        'Настройки': 'Settings',
        'Версии': 'Versions',
        'Новости': 'News',
        'Статистика': 'Statistics',
        'Общие настройки': 'General settings',
        'Включить режим обслуживания': 'Enable maintenance mode',
        'Сообщение при техработах': 'Maintenance message',
        'Ссылка для скачивания лаунчера': 'Launcher download link',
        'Java — задаётся один раз, скачивается всеми клиентами': 'Java — set once, downloaded by all clients',
        'Хэш': 'Hash',
        'Текущая версия лаунчера': 'Current launcher version',
        'URL сайта (для API)': 'Site URL (for API)',
        'Сохранить настройки': 'Save settings',
        'Социальные сети': 'Social networks',
        'Добавить версию': 'Add version',
        'Номер версии': 'Version number',
        'Имя папки (C:\\SkeetGuard\\...)': 'Folder name (C:\\SkeetGuard\\...)',
        'Имя папки': 'Folder name',
        'PouchLeaked (авто)': 'PouchLeaked (auto)',
        'Ссылка на JSON': 'JSON link',
        'URL fake.jar (фейковый jar для поставки)': 'fake.jar URL (fake jar for delivery)',
        'Crypto Key (обфускатор)': 'Crypto Key (obfuscator)',
        'KEY из обфускатора': 'KEY from obfuscator',
        'Список версий': 'Version list',
        'Версия': 'Version',
        'Папка': 'Folder',
        'java (общий)': 'java (shared)',
        'Удалить версию?': 'Delete version?',
        'Добавить новость': 'Add news',
        'Заголовок': 'Title',
        'Заголовок новости': 'News title',
        'Содержание': 'Content',
        'Текст новости...': 'News text...',
        'Добавить': 'Add',
        'Список новостей': 'News list',
        'Нет новостей': 'No news',
        'Удалить новость?': 'Delete news?',
        'Запуски за 7 дней': 'Launches in 7 days',
        'Последние запуски': 'Recent launches',
        'Редактирование версии': 'Edit version',
        'Активна (лаунчер скачивает файлы этой версии)': 'Active (the launcher downloads this version files)',
        'Версия удалена': 'Version deleted',
        'Новость удалена': 'News deleted',
        'Введите номер версии': 'Enter version number',
        'Такая версия уже существует': 'This version already exists',
        'Версия добавлена (хэши:': 'Version added (hashes:',
        'Версия обновлена (хэши:': 'Version updated (hashes:',
        'Новость добавлена': 'News added',
        'Введите URL': 'Enter URL',
        'Ошибка:': 'Error:'
    };

    /* ---------- НАСТРОЙКИ ---------- */
    var STORAGE_KEY = 'aial_lang';
    var current = 'ru';
    try { current = localStorage.getItem(STORAGE_KEY) || 'ru'; } catch (e) {}
    if (current !== 'ru' && current !== 'en') current = 'ru';

    var keys = Object.keys(D).sort(function (a, b) { return b.length - a.length; });

    function collapse(s) {
        return String(s || '').replace(/\s+/g, ' ').trim();
    }

    function isWordChar(ch) {
        return !!ch && /[a-zA-Zа-яА-ЯёЁ0-9_]/.test(ch);
    }

    /* ---------- ПЕРЕВОД ТЕКСТОВЫХ УЗЛОВ (подстрока с границами слов) ---------- */
    function translateText(text) {
        for (var i = 0; i < keys.length; i++) {
            var k = keys[i];
            var idx = text.indexOf(k);
            if (idx === -1) continue;
            var before = idx > 0 ? text.charAt(idx - 1) : '';
            var after = idx + k.length < text.length ? text.charAt(idx + k.length) : '';
            if (!isWordChar(before) && !isWordChar(after)) {
                text = text.slice(0, idx) + D[k] + text.slice(idx + k.length);
            }
        }
        return text;
    }

    function translateNodes() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                var p = node.parentNode;
                if (!p) return NodeFilter.FILTER_REJECT;
                var tag = p.nodeName;
                if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'IFRAME' || tag === 'NOSCRIPT' || tag === 'TEXTAREA') return NodeFilter.FILTER_REJECT;
                var t = collapse(node.nodeValue);
                if (!t) return NodeFilter.FILTER_REJECT;
                for (var i = 0; i < keys.length; i++) {
                    var k = keys[i];
                    if (t.indexOf(k) !== -1) return NodeFilter.FILTER_ACCEPT;
                }
                return NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) {
            if (!n.__ruText) n.__ruText = n.nodeValue;
            var fresh = translateText(n.__ruText);
            if (fresh !== n.nodeValue) n.nodeValue = fresh;
        }
    }

    /* ---------- ПЕРЕВОД АТРИБУТОВ placeholder / title / aria-label ---------- */
    function translateAttrs() {
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (!el.__ruAttrs) el.__ruAttrs = {};
                var v = collapse(el.getAttribute(attrs[i]));
                if (v && D[v]) {
                    if (!el.__ruAttrs[attrs[i]]) el.__ruAttrs[attrs[i]] = el.getAttribute(attrs[i]);
                    el.setAttribute(attrs[i], D[v]);
                }
            }
        }
    }

    /* ---------- ВОССТАНОВЛЕНИЕ РУССКОГО ТЕКСТА (без перезагрузки) ---------- */
    function restoreRu() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return node.__ruText !== undefined ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) {
            n.nodeValue = n.__ruText;
        }
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (el.__ruAttrs && el.__ruAttrs[attrs[i]] !== undefined) {
                    el.setAttribute(attrs[i], el.__ruAttrs[attrs[i]]);
                }
            }
        }
    }

    /* ---------- КНОПКИ RU/EN В ШАПКЕ ---------- */
    function buildSwitcher() {
        var host = document.querySelector('.header-actions');
        if (!host) host = document.querySelector('.header-nav');
        if (!host) return;
        if (document.querySelector('.lang-switch')) return;

        var style = document.createElement('style');
        style.textContent = '.lang-switch{display:inline-flex;align-items:center;gap:2px;padding:3px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);border-radius:999px;margin-right:.15rem;}' +
            '.lang-btn{border:0;background:transparent;color:rgba(255,255,255,.4);font-family:Inter,sans-serif;font-size:.72rem;font-weight:600;line-height:1;padding:5px 8px;border-radius:999px;cursor:pointer;transition:color .18s ease,background .18s ease;}' +
            '.lang-btn:hover{color:#fff;}' +
            '.lang-btn.active{color:#fff;background:rgba(255,255,255,.1);}';
        document.head.appendChild(style);

        var wrap = document.createElement('div');
        wrap.className = 'lang-switch';

        function make(lng, label, title) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'lang-btn' + (current === lng ? ' active' : '');
            b.textContent = label;
            b.title = title;
            b.addEventListener('click', function () { setLang(lng); });
            return b;
        }
        wrap.appendChild(make('ru', 'RU', 'Русский'));
        wrap.appendChild(make('en', 'EN', 'English'));

        var ref = host.querySelector('.burger') || host.querySelector('.user-chip');
        if (ref) host.insertBefore(wrap, ref);
        else host.appendChild(wrap);
    }

    function setLang(lng) {
        if (lng === current) return;
        current = lng;
        try { localStorage.setItem(STORAGE_KEY, lng); } catch (e) {}
        document.documentElement.setAttribute('lang', lng === 'ru' ? 'ru' : 'en');
        var btns = document.querySelectorAll('.lang-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('active', btns[i].textContent === (lng === 'ru' ? 'RU' : 'EN'));
        }
        if (lng === 'en') {
            translateNodes();
            translateAttrs();
        } else {
            restoreRu();
        }
    }

    function boot() {
        document.documentElement.setAttribute('lang', current === 'ru' ? 'ru' : 'en');
        buildSwitcher();
        if (current === 'en') {
            translateNodes();
            translateAttrs();
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();

    window.__lang = current;
})();