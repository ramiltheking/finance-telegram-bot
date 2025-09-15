<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['type' => 'income', 'name_en' => 'Salary', 'name_ru' => 'Зарплата', 'emoji' => '💼'],
            ['type' => 'income', 'name_en' => 'Freelance', 'name_ru' => 'Фриланс', 'emoji' => '🧑‍💻'],
            ['type' => 'income', 'name_en' => 'Investments', 'name_ru' => 'Инвестиции', 'emoji' => '📈'],
            ['type' => 'income', 'name_en' => 'Rent', 'name_ru' => 'Аренда недвижимости', 'emoji' => '🏠'],
            ['type' => 'income', 'name_en' => 'Sales', 'name_ru' => 'Продажа товаров', 'emoji' => '🛒'],
            ['type' => 'income', 'name_en' => 'Side Jobs', 'name_ru' => 'Подработки', 'emoji' => '🛠️'],
            ['type' => 'income', 'name_en' => 'Gifts', 'name_ru' => 'Подарки', 'emoji' => '🎁'],
            ['type' => 'income', 'name_en' => 'Social Payments', 'name_ru' => 'Социальные выплаты', 'emoji' => '💸'],
            ['type' => 'income', 'name_en' => 'Cashback', 'name_ru' => 'Кэшбэк / бонусы', 'emoji' => '💳'],
            ['type' => 'income', 'name_en' => 'Online Projects', 'name_ru' => 'Доход от онлайн-проектов', 'emoji' => '💻'],
            ['type' => 'income', 'name_en' => 'Royalties', 'name_ru' => 'Роялти', 'emoji' => '🎶'],
            ['type' => 'income', 'name_en' => 'Debt Return', 'name_ru' => 'Возврат долгов', 'emoji' => '🔄'],
            ['type' => 'income', 'name_en' => 'Prizes', 'name_ru' => 'Призы, выигрыши', 'emoji' => '🏆'],
            ['type' => 'income', 'name_en' => 'Currency Exchange Profit', 'name_ru' => 'Обмен валюты (прибыль)', 'emoji' => '💱'],
            ['type' => 'income', 'name_en' => 'Digital Assets Sale', 'name_ru' => 'Продажа цифровых активов', 'emoji' => '💎'],

            ['type' => 'expense', 'name_en' => 'Housing', 'name_ru' => 'Дом и быт', 'emoji' => '🏠'],
            ['type' => 'expense', 'name_en' => 'Rent / Mortgage', 'name_ru' => 'Аренда жилья / ипотека', 'emoji' => '🏡'],
            ['type' => 'expense', 'name_en' => 'Utilities', 'name_ru' => 'Коммунальные услуги', 'emoji' => '⚡'],
            ['type' => 'expense', 'name_en' => 'Internet & Mobile', 'name_ru' => 'Интернет и связь', 'emoji' => '📶'],
            ['type' => 'expense', 'name_en' => 'Household Goods', 'name_ru' => 'Хозяйственные товары', 'emoji' => '🧹'],
            ['type' => 'expense', 'name_en' => 'Furniture & Appliances', 'name_ru' => 'Мебель, техника', 'emoji' => '🛋'],

            ['type' => 'expense', 'name_en' => 'Clothes', 'name_ru' => 'Одежда и обувь', 'emoji' => '👗'],
            ['type' => 'expense', 'name_en' => 'Beauty & Care', 'name_ru' => 'Косметика и уход', 'emoji' => '💄'],
            ['type' => 'expense', 'name_en' => 'Hairdresser', 'name_ru' => 'Парикмахер, салоны', 'emoji' => '💇‍♂️'],
            ['type' => 'expense', 'name_en' => 'Gifts to Others', 'name_ru' => 'Подарки другим', 'emoji' => '🎁'],
            ['type' => 'expense', 'name_en' => 'Pets', 'name_ru' => 'Домашние животные', 'emoji' => '🐾'],

            ['type' => 'expense', 'name_en' => 'Groceries', 'name_ru' => 'Продукты', 'emoji' => '🍎'],
            ['type' => 'expense', 'name_en' => 'Restaurants', 'name_ru' => 'Кафе и рестораны', 'emoji' => '🍕'],
            ['type' => 'expense', 'name_en' => 'Coffee & Snacks', 'name_ru' => 'Кофе и перекусы', 'emoji' => '☕'],
            ['type' => 'expense', 'name_en' => 'Food Delivery', 'name_ru' => 'Доставка еды', 'emoji' => '🚚'],

            ['type' => 'expense', 'name_en' => 'Public Transport', 'name_ru' => 'Общественный транспорт', 'emoji' => '🚌'],
            ['type' => 'expense', 'name_en' => 'Taxi', 'name_ru' => 'Такси', 'emoji' => '🚕'],
            ['type' => 'expense', 'name_en' => 'Fuel', 'name_ru' => 'Бензин / зарядка', 'emoji' => '⛽'],
            ['type' => 'expense', 'name_en' => 'Car Maintenance', 'name_ru' => 'Обслуживание авто', 'emoji' => '🚗'],
            ['type' => 'expense', 'name_en' => 'Travel Tickets', 'name_ru' => 'Поездки (билеты)', 'emoji' => '🚆'],

            ['type' => 'expense', 'name_en' => 'Cinema & Theatre', 'name_ru' => 'Кино, театр', 'emoji' => '🎬'],
            ['type' => 'expense', 'name_en' => 'Games', 'name_ru' => 'Игры', 'emoji' => '🎮'],
            ['type' => 'expense', 'name_en' => 'Music & Concerts', 'name_ru' => 'Музыка, концерты', 'emoji' => '🎶'],
            ['type' => 'expense', 'name_en' => 'Sport & Fitness', 'name_ru' => 'Спорт, фитнес', 'emoji' => '⚽'],
            ['type' => 'expense', 'name_en' => 'Travel', 'name_ru' => 'Путешествия', 'emoji' => '✈️'],
            ['type' => 'expense', 'name_en' => 'Bars & Clubs', 'name_ru' => 'Бары, клубы', 'emoji' => '🍻'],

            ['type' => 'expense', 'name_en' => 'Books', 'name_ru' => 'Книги', 'emoji' => '📖'],
            ['type' => 'expense', 'name_en' => 'Courses', 'name_ru' => 'Курсы и обучение', 'emoji' => '🎓'],
            ['type' => 'expense', 'name_en' => 'Tutors', 'name_ru' => 'Репетиторы', 'emoji' => '👩‍🏫'],

            ['type' => 'expense', 'name_en' => 'Doctors', 'name_ru' => 'Врачи, клиники', 'emoji' => '🏥'],
            ['type' => 'expense', 'name_en' => 'Medicine', 'name_ru' => 'Лекарства', 'emoji' => '💊'],
            ['type' => 'expense', 'name_en' => 'Dentist', 'name_ru' => 'Стоматология', 'emoji' => '🦷'],
            ['type' => 'expense', 'name_en' => 'Fitness & Yoga', 'name_ru' => 'Фитнес и йога', 'emoji' => '🧘‍♂️'],

            ['type' => 'expense', 'name_en' => 'Smartphones & Gadgets', 'name_ru' => 'Смартфоны, гаджеты', 'emoji' => '📱'],
            ['type' => 'expense', 'name_en' => 'Computers', 'name_ru' => 'Компьютеры', 'emoji' => '💻'],
            ['type' => 'expense', 'name_en' => 'Subscriptions', 'name_ru' => 'Подписки', 'emoji' => '📦'],
            ['type' => 'expense', 'name_en' => 'Online Services', 'name_ru' => 'Онлайн-сервисы и ПО', 'emoji' => '☁️'],

            ['type' => 'expense', 'name_en' => 'Credits & Debts', 'name_ru' => 'Кредиты, долги', 'emoji' => '🏦'],
            ['type' => 'expense', 'name_en' => 'Transfers', 'name_ru' => 'Переводы', 'emoji' => '💸'],
            ['type' => 'expense', 'name_en' => 'Investments Purchase', 'name_ru' => 'Инвестиции (покупка активов)', 'emoji' => '📉'],
            ['type' => 'expense', 'name_en' => 'Insurance', 'name_ru' => 'Страховка', 'emoji' => '🔐'],
            ['type' => 'expense', 'name_en' => 'Currency Exchange', 'name_ru' => 'Обмен валют', 'emoji' => '💱'],
        ];

        DB::table('categories')->insert($categories);
    }
}
