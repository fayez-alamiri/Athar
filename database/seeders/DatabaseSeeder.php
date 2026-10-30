<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed ATHAR with six Arabic demo publisher accounts and curated quotations.
     *
     * This seeder is intentionally non-destructive:
     * - It uses updateOrCreate() so it can safely be run more than once.
     * - It never deletes users, quotes, books, authors, likes, or saved quotes.
     * - It is intended for local/demo databases only; do not run it on production.
     */
    public function run(): void
    {
        $demoPassword = 'AtharDemo2026!';

        $accounts = [
            'adab' => [
                'name' => 'عاشق الأدب',
                'email' => 'adab@athar.local',
            ],
            'wisdom' => [
                'name' => 'طالب الحكمة',
                'email' => 'wisdom@athar.local',
            ],
            'night' => [
                'name' => 'قارئ الليل',
                'email' => 'nightreader@athar.local',
            ],
            'knowledge' => [
                'name' => 'طالب العلم',
                'email' => 'student@athar.local',
            ],
            'reading' => [
                'name' => 'نبض القراءة',
                'email' => 'reading@athar.local',
            ],
            'ink' => [
                'name' => 'حبر العلم',
                'email' => 'ink@athar.local',
            ],
        ];

        $users = [];

        foreach ($accounts as $key => $account) {
            $users[$key] = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $demoPassword,
                    'locale' => 'ar',
                ]
            );
        }

        $quotes = [
            // Quick quotations — classical Arabic poetry.
            [
                'publisher' => 'knowledge',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "شَكَوتُ إِلى وَكِيعٍ سُوءَ حِفظي\nفَأَرشَدَني إِلى تَركِ المَعاصي\nوَأَخبَرَني بِأَنَّ العِلمَ نورٌ\nوَنورُ اللهِ لا يُهدى لِعاصي",
                'attributed_author' => 'الإمام الشافعي (منسوب إليه)',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'adab',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "عَلى قَدرِ أَهلِ العَزمِ تَأتي العَزائِمُ\nوَتَأتي عَلى قَدرِ الكِرامِ المَكارِمُ",
                'attributed_author' => 'المتنبي',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'adab',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "إِذا غامَرتَ في شَرَفٍ مَرومٍ\nفَلا تَقنَعْ بِما دونَ النُّجومِ",
                'attributed_author' => 'المتنبي',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'adab',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "الخَيلُ وَاللَّيلُ وَالبَيداءُ تَعرِفُني\nوَالسَّيفُ وَالرُّمحُ وَالقِرطاسُ وَالقَلَمُ",
                'attributed_author' => 'المتنبي',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'night',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "ما كُلُّ ما يَتَمَنّى المَرءُ يُدرِكُهُ\nتَجري الرِّياحُ بِما لا تَشتَهي السُّفُنُ",
                'attributed_author' => 'المتنبي',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'reading',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "إِنَّما الأُمَمُ الأَخلاقُ ما بَقِيَتْ\nفَإِنْ هُمُ ذَهَبَتْ أَخلاقُهُمْ ذَهَبوا",
                'attributed_author' => 'أحمد شوقي',
                'book_title' => null,
                'book_cover_path' => null,
            ],
            [
                'publisher' => 'wisdom',
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "يُخاطِبُني السَّفيهُ بِكُلِّ قُبحٍ\nفَأَكرَهُ أَنْ أَكونَ لَهُ مُجيبًا\nيَزيدُ سَفاهَةً فَأَزيدُ حِلمًا\nكَعودٍ زادَهُ الإِحراقُ طيبًا",
                'attributed_author' => 'الإمام الشافعي (منسوب إليه)',
                'book_title' => null,
                'book_cover_path' => null,
            ],

            // Book quotations — the credited speaker is stored in attributed_author.
            [
                'publisher' => 'knowledge',
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'اعْلَمْ رَحِمَكَ اللهُ أَنَّهُ يَجِبُ عَلَيْنَا تَعَلُّمُ أَرْبَعِ مَسَائِلَ: الْعِلْمُ، وَالْعَمَلُ بِهِ، وَالدَّعْوَةُ إِلَيْهِ، وَالصَّبْرُ عَلَى الْأَذَى فِيهِ.',
                'attributed_author' => 'محمد بن عبد الوهاب',
                'book_title' => 'ثلاثة الأصول وأدلتها',
                'book_cover_path' => 'images/books/three-fundamental-principles.jfif',
            ],
            [
                'publisher' => 'reading',
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'يَا رَبِّ إِنَّ ذُنُوبِي عَظِيمَةٌ، وَإِنَّ قَلِيلَ عَفْوِكَ أَعْظَمُ مِنْهَا، اللَّهُمَّ فَامْحُ بِقَلِيلِ عَفْوِكَ عَظِيمَ ذُنُوبِي.',
                'attributed_author' => 'عبد الملك بن مروان',
                'book_title' => 'البداية والنهاية',
                'book_cover_path' => 'images/books/al-bidaya-wa-al-nihaya.jfif',
            ],
            [
                'publisher' => 'ink',
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'لَا فَوْزَ إِلَّا فِي طَاعَتِهِ، وَلَا عِزَّ إِلَّا فِي التَّذَلُّلِ لِعَظَمَتِهِ، وَلَا غِنَى إِلَّا فِي الِافْتِقَارِ إِلَى رَحْمَتِهِ.',
                'attributed_author' => 'ابن قيم الجوزية',
                'book_title' => 'زاد المعاد في هدي خير العباد',
                'book_cover_path' => 'images/books/zad-al-maad.jfif',
            ],
            [
                'publisher' => 'wisdom',
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'كل انطفأ حلم خلق الله لك حلما أجمل... وكلما بهتت في قلبك ذكرى صنع الله لك ذكرى أروع!',
                'attributed_author' => 'علي بن جابر الفيفي',
                'book_title' => 'لأنك الله: رحلة إلى السماء السابعة',
                'book_cover_path' => 'images/books/lanak-allah.jpg',
            ],
        ];

        foreach ($quotes as $quoteData) {
            $publisher = $users[$quoteData['publisher']];

            $quote = Quote::updateOrCreate(
                [
                    'user_id' => $publisher->id,
                    'type' => $quoteData['type'],
                    'quote_text' => $quoteData['quote_text'],
                ],
                [
                    'attributed_author' => $quoteData['attributed_author'],
                    'book_title' => $quoteData['book_title'],
                    'book_cover_path' => $quoteData['book_cover_path'],
                ]
            );

            // Keep the normalized authors/books catalog in sync with the legacy quote fields.
            $quote->syncCatalogRelations();
        }
    }
}
