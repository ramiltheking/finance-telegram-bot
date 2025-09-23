<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'INCOME' => [
                [
                    'name_en' => 'Work & Business',
                    'name_ru' => 'Работа и бизнес',
                    'slug'    => 'work-business',
                    'children' => [
                        ['name_en' => 'Salary', 'name_ru' => 'Зарплата', 'slug' => 'salary'],
                        ['name_en' => 'Freelance', 'name_ru' => 'Фриланс', 'slug' => 'freelance'],
                        ['name_en' => 'Side Jobs', 'name_ru' => 'Подработки', 'slug' => 'side-jobs'],
                        ['name_en' => 'Business Profit', 'name_ru' => 'Прибыль от бизнеса', 'slug' => 'business-profit'],
                        ['name_en' => 'Selling Services', 'name_ru' => 'Продажа услуг', 'slug' => 'selling-services'],
                    ],
                ],
                [
                    'name_en' => 'Investments & Capital',
                    'name_ru' => 'Инвестиции и капитал',
                    'slug'    => 'investments-capital',
                    'children' => [
                        ['name_en' => 'Investments', 'name_ru' => 'Инвестиции', 'slug' => 'investments'],
                        ['name_en' => 'Dividends', 'name_ru' => 'Дивиденды', 'slug' => 'dividends'],
                        ['name_en' => 'Currency Exchange Profit', 'name_ru' => 'Доход от валюты', 'slug' => 'currency-profit'],
                        ['name_en' => 'Digital Assets Sale', 'name_ru' => 'Продажа цифровых активов', 'slug' => 'digital-assets-sale'],
                        ['name_en' => 'Royalties', 'name_ru' => 'Роялти', 'slug' => 'royalties'],
                        ['name_en' => 'Intellectual Property Sale', 'name_ru' => 'Продажа интеллектуальной собственности', 'slug' => 'ip-sale'],
                    ],
                ],
                [
                    'name_en' => 'Real Estate & Rent',
                    'name_ru' => 'Недвижимость и аренда',
                    'slug'    => 'real-estate-rent',
                    'children' => [
                        ['name_en' => 'Rent', 'name_ru' => 'Аренда', 'slug' => 'rent'],
                        ['name_en' => 'Rental Equipment/Transport', 'name_ru' => 'Аренда техники/транспорта', 'slug' => 'rental-equipment'],
                    ],
                ],
                [
                    'name_en' => 'Social & Personal',
                    'name_ru' => 'Социальные и личные',
                    'slug'    => 'social-personal',
                    'children' => [
                        ['name_en' => 'Social Payments', 'name_ru' => 'Социальные выплаты', 'slug' => 'social-payments'],
                        ['name_en' => 'Pension', 'name_ru' => 'Пенсия', 'slug' => 'pension'],
                        ['name_en' => 'Scholarship / Grant', 'name_ru' => 'Стипендия / грант', 'slug' => 'scholarship'],
                        ['name_en' => 'Gifts', 'name_ru' => 'Подарки', 'slug' => 'gifts'],
                        ['name_en' => 'Prizes', 'name_ru' => 'Призы', 'slug' => 'prizes'],
                        ['name_en' => 'Inheritance', 'name_ru' => 'Наследство', 'slug' => 'inheritance'],
                    ],
                ],
                [
                    'name_en' => 'Refunds & Donations',
                    'name_ru' => 'Возвраты и донаты',
                    'slug'    => 'refunds-donations',
                    'children' => [
                        ['name_en' => 'Debt Return', 'name_ru' => 'Возврат долгов', 'slug' => 'debt-return'],
                        ['name_en' => 'Refunds & Compensations', 'name_ru' => 'Возвраты и компенсации', 'slug' => 'refunds'],
                        ['name_en' => 'Crowdfunding / Donations Received', 'name_ru' => 'Пожертвования / донаты', 'slug' => 'donations'],
                        ['name_en' => 'Affiliate & Advertising', 'name_ru' => 'Партнёрки и реклама', 'slug' => 'affiliate'],
                        ['name_en' => 'Online Projects', 'name_ru' => 'Онлайн-проекты', 'slug' => 'online-projects'],
                        ['name_en' => 'Cashback', 'name_ru' => 'Кэшбэк', 'slug' => 'cashback'],
                    ],
                ],
                [
                    'name_en' => 'Loans',
                    'name_ru' => 'Займы',
                    'slug'    => 'loans',
                    'children' => [
                        ['name_en' => 'Loans Received', 'name_ru' => 'Полученные займы', 'slug' => 'loans-received'],
                    ],
                ],
            ],
            'EXPENSE' => [
                [
                    'name_en' => 'Housing & Utilities',
                    'name_ru' => 'Жильё и коммуналка',
                    'slug'    => 'housing-utilities',
                    'children' => [
                        ['name_en' => 'Housing', 'name_ru' => 'Жильё', 'slug' => 'housing'],
                        ['name_en' => 'Rent / Mortgage', 'name_ru' => 'Аренда / ипотека', 'slug' => 'rent-mortgage'],
                        ['name_en' => 'Utilities', 'name_ru' => 'Коммунальные услуги', 'slug' => 'utilities'],
                        ['name_en' => 'Internet & Mobile', 'name_ru' => 'Интернет и связь', 'slug' => 'internet-mobile'],
                        ['name_en' => 'Household Goods', 'name_ru' => 'Товары для дома', 'slug' => 'household-goods'],
                        ['name_en' => 'Furniture & Appliances', 'name_ru' => 'Мебель и техника', 'slug' => 'furniture-appliances'],
                        ['name_en' => 'Home Renovation & Repairs', 'name_ru' => 'Ремонт и благоустройство', 'slug' => 'home-renovation'],
                        ['name_en' => 'Home Security', 'name_ru' => 'Охрана дома', 'slug' => 'home-security'],
                    ],
                ],
                [
                    'name_en' => 'Personal',
                    'name_ru' => 'Личное',
                    'slug'    => 'personal',
                    'children' => [
                        ['name_en' => 'Clothes', 'name_ru' => 'Одежда', 'slug' => 'clothes'],
                        ['name_en' => 'Beauty & Care', 'name_ru' => 'Красота и уход', 'slug' => 'beauty-care'],
                        ['name_en' => 'Hairdresser', 'name_ru' => 'Парикмахер', 'slug' => 'hairdresser'],
                        ['name_en' => 'Smartphones & Gadgets', 'name_ru' => 'Смартфоны и гаджеты', 'slug' => 'smartphones-gadgets'],
                        ['name_en' => 'Computers', 'name_ru' => 'Компьютеры', 'slug' => 'computers'],
                        ['name_en' => 'Hobbies & Collections', 'name_ru' => 'Хобби и коллекции', 'slug' => 'hobbies'],
                    ],
                ],
                [
                    'name_en' => 'Family & Children',
                    'name_ru' => 'Семья и дети',
                    'slug'    => 'family-children',
                    'children' => [
                        ['name_en' => 'Children & Education', 'name_ru' => 'Дети и образование', 'slug' => 'children-education'],
                        ['name_en' => 'Tutors', 'name_ru' => 'Репетиторы', 'slug' => 'tutors'],
                        ['name_en' => 'Courses', 'name_ru' => 'Курсы', 'slug' => 'courses'],
                        ['name_en' => 'Gifts to Others', 'name_ru' => 'Подарки другим', 'slug' => 'gifts-others'],
                        ['name_en' => 'Pets', 'name_ru' => 'Животные', 'slug' => 'pets'],
                    ],
                ],
                [
                    'name_en' => 'Food & Leisure',
                    'name_ru' => 'Еда и развлечения',
                    'slug'    => 'food-leisure',
                    'children' => [
                        ['name_en' => 'Groceries', 'name_ru' => 'Продукты', 'slug' => 'groceries'],
                        ['name_en' => 'Restaurants', 'name_ru' => 'Рестораны', 'slug' => 'restaurants'],
                        ['name_en' => 'Coffee & Snacks', 'name_ru' => 'Кофе и перекусы', 'slug' => 'coffee-snacks'],
                        ['name_en' => 'Food Delivery', 'name_ru' => 'Доставка еды', 'slug' => 'food-delivery'],
                        ['name_en' => 'Bars & Clubs', 'name_ru' => 'Бары и клубы', 'slug' => 'bars-clubs'],
                        ['name_en' => 'Cinema & Theatre', 'name_ru' => 'Кино и театр', 'slug' => 'cinema-theatre'],
                        ['name_en' => 'Music & Concerts', 'name_ru' => 'Музыка и концерты', 'slug' => 'music-concerts'],
                        ['name_en' => 'Games', 'name_ru' => 'Игры', 'slug' => 'games'],
                        ['name_en' => 'Travel', 'name_ru' => 'Путешествия', 'slug' => 'travel'],
                        ['name_en' => 'Travel Tickets', 'name_ru' => 'Билеты на поездки', 'slug' => 'travel-tickets'],
                        ['name_en' => 'Books', 'name_ru' => 'Книги', 'slug' => 'books'],
                        ['name_en' => 'Streaming & Entertainment Subscriptions', 'name_ru' => 'Подписки на развлечения', 'slug' => 'streaming'],
                    ],
                ],
                [
                    'name_en' => 'Transport',
                    'name_ru' => 'Транспорт',
                    'slug'    => 'transport',
                    'children' => [
                        ['name_en' => 'Public Transport', 'name_ru' => 'Общественный транспорт', 'slug' => 'public-transport'],
                        ['name_en' => 'Taxi', 'name_ru' => 'Такси', 'slug' => 'taxi'],
                        ['name_en' => 'Fuel', 'name_ru' => 'Топливо', 'slug' => 'fuel'],
                        ['name_en' => 'Car Maintenance', 'name_ru' => 'Обслуживание авто', 'slug' => 'car-maintenance'],
                        ['name_en' => 'Parking & Tolls', 'name_ru' => 'Парковка и платные дороги', 'slug' => 'parking-tolls'],
                    ],
                ],
                [
                    'name_en' => 'Health',
                    'name_ru' => 'Здоровье',
                    'slug'    => 'health',
                    'children' => [
                        ['name_en' => 'Doctors', 'name_ru' => 'Врачи', 'slug' => 'doctors'],
                        ['name_en' => 'Dentist', 'name_ru' => 'Стоматолог', 'slug' => 'dentist'],
                        ['name_en' => 'Medicine', 'name_ru' => 'Медицина', 'slug' => 'medicine'],
                        ['name_en' => 'Fitness & Yoga', 'name_ru' => 'Фитнес и йога', 'slug' => 'fitness-yoga'],
                        ['name_en' => 'Sport & Fitness', 'name_ru' => 'Спорт и фитнес', 'slug' => 'sport-fitness'],
                        ['name_en' => 'Health Insurance', 'name_ru' => 'Медицинская страховка', 'slug' => 'health-insurance'],
                    ],
                ],
                [
                    'name_en' => 'Finance & Obligations',
                    'name_ru' => 'Финансы и обязательства',
                    'slug'    => 'finance-obligations',
                    'children' => [
                        ['name_en' => 'Credits & Debts', 'name_ru' => 'Кредиты и долги', 'slug' => 'credits-debts'],
                        ['name_en' => 'Transfers', 'name_ru' => 'Переводы', 'slug' => 'transfers'],
                        ['name_en' => 'Investments Purchase', 'name_ru' => 'Покупка инвестиций', 'slug' => 'investments-purchase'],
                        ['name_en' => 'Insurance', 'name_ru' => 'Страхование', 'slug' => 'insurance'],
                        ['name_en' => 'Currency Exchange', 'name_ru' => 'Обмен валюты', 'slug' => 'currency-exchange'],
                        ['name_en' => 'Loans Given', 'name_ru' => 'Выданные займы', 'slug' => 'loans-given'],
                        ['name_en' => 'Taxes', 'name_ru' => 'Налоги', 'slug' => 'taxes'],
                        ['name_en' => 'Bank Fees & Commissions', 'name_ru' => 'Банковские комиссии', 'slug' => 'bank-fees'],
                        ['name_en' => 'Legal Services & Fines', 'name_ru' => 'Юр. услуги и штрафы', 'slug' => 'legal-services'],
                    ],
                ],
                [
                    'name_en' => 'Other',
                    'name_ru' => 'Прочее',
                    'slug'    => 'other',
                    'children' => [
                        ['name_en' => 'Subscriptions', 'name_ru' => 'Подписки', 'slug' => 'subscriptions'],
                        ['name_en' => 'Online Services', 'name_ru' => 'Онлайн-сервисы', 'slug' => 'online-services'],
                        ['name_en' => 'Business Expenses', 'name_ru' => 'Бизнес-расходы', 'slug' => 'business-expenses'],
                        ['name_en' => 'Charity & Donations', 'name_ru' => 'Благотворительность', 'slug' => 'charity'],
                        ['name_en' => 'Alcohol & Tobacco', 'name_ru' => 'Алкоголь и табак', 'slug' => 'alcohol-tobacco'],
                        ['name_en' => 'Gambling & Lottery', 'name_ru' => 'Азартные игры и лотереи', 'slug' => 'gambling'],
                    ],
                ],
            ]
        ];

        foreach ($categories as $type => $groups) {
            foreach ($groups as $group) {
                $parent = Category::create([
                    'parent_id' => null,
                    'type'      => $type,
                    'name_en'   => $group['name_en'],
                    'name_ru'   => $group['name_ru'],
                    'slug'      => $group['slug'],
                ]);

                foreach ($group['children'] as $child) {
                    Category::create([
                        'parent_id' => $parent->id,
                        'type'      => $type,
                        'name_en'   => $child['name_en'],
                        'name_ru'   => $child['name_ru'],
                        'slug'      => $child['slug'],
                    ]);
                }
            }
        }
    }
}
