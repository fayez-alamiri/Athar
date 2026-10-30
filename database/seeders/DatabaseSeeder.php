<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Non-destructively seed or update sample readers, quick quotes (with attributed authors),
     * and book quotes (with their configured public/images/books/*.jfif cover paths).
     */
    public function run(): void
    {
        $arabicReader = User::updateOrCreate(
            ['email' => 'reader@quoteit.local'],
            [
                'name' => 'أديب القراءة',
                'password' => 'password123',
                'locale' => 'ar',
            ]
        );

        $englishReader = User::updateOrCreate(
            ['email' => 'editorial@quoteit.local'],
            [
                'name' => 'Clara Vance',
                'password' => 'password123',
                'locale' => 'en',
            ]
        );

        // 1. Quick User Quotes (with attributed authors in Arabic and English)
        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => "على قدر أهل العزم تأتي العزائم\nوتأتي على قدر الكرام المكارم",
            ],
            [
                'attributed_author' => 'المتنبي',
                'book_title' => null,
                'book_cover_path' => null,
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $englishReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => 'The only true wisdom is in knowing you know nothing.',
            ],
            [
                'attributed_author' => 'سقراط',
                'book_title' => null,
                'book_cover_path' => null,
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => 'القراءة تمنحنا حياة أخرى نعيشها فوق حياتنا، وتفتح لنا نوافذ لا تغلقها المسافات.',
            ],
            [
                'attributed_author' => 'عباس محمود العقاد',
                'book_title' => null,
                'book_cover_path' => null,
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $englishReader->id,
                'type' => Quote::TYPE_QUICK,
                'quote_text' => 'Quiet libraries hold the loudest conversations across centuries of human thought.',
            ],
            [
                'attributed_author' => 'Jorge Luis Borges',
                'book_title' => null,
                'book_cover_path' => null,
            ]
        );

        // 2. Book Quotes with configured public/images/books/*.jfif cover paths
        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'إن الكلمات التي تخرج من القلب تدخل إلى القلب، وما يخرج من اللسان لا يتجاوز الآذان.',
            ],
            [
                'book_title' => 'وحي القلم',
                'attributed_author' => 'مصطفى صادق الرافعي',
                'book_cover_path' => 'images/books/wahy-al-qalam.jfif',
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'اعلم رحمك الله أنه يجب علينا تعلم أربع مسائل: الأولى العلم، والثانية العمل به، والثالثة الدعوة إليه، والرابعة الصبر على الأذى فيه.',
            ],
            [
                'book_title' => 'ثلاثة الأصول وأدلتها',
                'attributed_author' => 'محمد بن عبد الوهاب',
                'book_cover_path' => 'images/books/three-fundamental-principles.jfif',
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'إنما الأعمال بالنيات، وإنما لكل امرئ ما نوى.',
            ],
            [
                'book_title' => 'صحيح البخاري',
                'attributed_author' => 'الإمام محمد بن إسماعيل البخاري',
                'book_cover_path' => 'images/books/sahih-al-bukhari.jfif',
            ]
        );

        Quote::updateOrCreate(
            [
                'user_id' => $arabicReader->id,
                'type' => Quote::TYPE_BOOK,
                'quote_text' => 'الدين النصيحة، قلنا لمن؟ قال: لله ولكتابه ولرسوله ولأئمة المسلمين وعامتهم.',
            ],
            [
                'book_title' => 'صحيح مسلم',
                'attributed_author' => 'الإمام مسلم بن الحجاج',
                'book_cover_path' => 'images/books/sahih-muslim.jfif',
            ]
        );

        // Backfill any existing book quotes matching the 4 seeded book titles that still have a null cover path
        $titleCoverMap = [
            'وحي القلم' => 'images/books/wahy-al-qalam.jfif',
            'ثلاثة الأصول وأدلتها' => 'images/books/three-fundamental-principles.jfif',
            'الأصول الثلاثة' => 'images/books/three-fundamental-principles.jfif',
            'Three Fundamental Principles' => 'images/books/three-fundamental-principles.jfif',
            'صحيح البخاري' => 'images/books/sahih-al-bukhari.jfif',
            'Sahih al-Bukhari' => 'images/books/sahih-al-bukhari.jfif',
            'صحيح مسلم' => 'images/books/sahih-muslim.jfif',
            'Sahih Muslim' => 'images/books/sahih-muslim.jfif',
        ];

        foreach ($titleCoverMap as $title => $coverPath) {
            Quote::query()
                ->where('type', Quote::TYPE_BOOK)
                ->where('book_title', $title)
                ->where(function ($q) {
                    $q->whereNull('book_cover_path')->orWhere('book_cover_path', '');
                })
                ->update(['book_cover_path' => $coverPath]);
        }

        Quote::query()->get()->each->syncCatalogRelations();
    }
}