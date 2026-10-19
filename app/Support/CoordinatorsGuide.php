<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * The Coordinators Guide is the internal handbook that explains what the work
 * is: who does what, where each module lives and what a good day looks like.
 *
 * It is written in markdown and stored in the settings table, so a super admin
 * can rewrite it from the admin area without a deployment. Until somebody
 * saves their own version the built-in outline below is what people read.
 */
class CoordinatorsGuide
{
    /** Where the edited document is kept. */
    public const SETTING_KEY = 'coordinators_guide.body';

    public static function document(): string
    {
        $stored = Setting::query()->where('key', self::SETTING_KEY)->value('value');

        return filled($stored) ? (string) $stored : self::defaultDocument();
    }

    /**
     * Whether anybody has saved their own version of the guide.
     */
    public static function isCustomised(): bool
    {
        return filled(Setting::query()->where('key', self::SETTING_KEY)->value('value'));
    }

    public static function save(string $markdown): void
    {
        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => trim($markdown)]
        );
    }

    /**
     * Render the markdown and collect its headings for the table of contents.
     *
     * @return array{html: string, headings: list<array{level: int, title: string, anchor: string}>}
     */
    public static function render(string $markdown): array
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $headings = [];
        $used = [];

        $html = (string) preg_replace_callback(
            '/<h([2-4])>(.*?)<\/h\1>/s',
            function (array $match) use (&$headings, &$used): string {
                $level = (int) $match[1];
                $title = trim(html_entity_decode(strip_tags($match[2])));
                $anchor = Str::slug($title) ?: 'section';

                $used[$anchor] = ($used[$anchor] ?? 0) + 1;

                if ($used[$anchor] > 1) {
                    $anchor .= '-'.$used[$anchor];
                }

                $headings[] = ['level' => $level, 'title' => $title, 'anchor' => $anchor];

                return sprintf('<h%d id="%s">%s</h%d>', $level, $anchor, $match[2], $level);
            },
            $html
        );

        return ['html' => $html, 'headings' => $headings];
    }

    public static function defaultDocument(): string
    {
        return <<<'MARKDOWN'
## What this guide is for

This is the working handbook for everyone who helps run My Leader Kenya. It explains what the
platform is here to do, which part of the admin area handles which job, and what a good day of
work looks like. Read it once end to end, then come back to the section you need.

Nothing here replaces the system itself: if the guide and the software disagree, the software is
right. Tell a super admin so this page can be corrected.

## The job in one paragraph

We help Kenyans verify that they are registered voters, understand who is asking for their vote,
and take part in the conversation. Everything in the admin area exists to serve one of three
promises: the voter data is accurate, the public content is trustworthy, and candidates are
treated fairly.

## Getting started

### Signing in

Use the email address and password a super admin created for you. If your session expires you
will be sent back to the sign-in page with a warning, so save any long text before you walk away
from a form.

### Your profile

Keep your name, phone number and county up to date under your account menu. Reports and audits
are recorded against you, so an accurate profile keeps the record honest.

### What you can see

The menu on the left only shows the modules your role has been granted. If a colleague can see
something you cannot, that is a permission decision rather than a fault - ask a super admin if
you need the access.

## Roles and permissions

Access is not hard coded. A super admin creates roles under **System Settings > Admins, Roles &
Permissions**, decides whether a role may enter the admin area at all, and ticks the permissions
that role holds. Two flags matter:

- **Grants admin access** - the person may sign in here at all.
- **Full access** - every permission check passes. Reserve this for super admins.

Every module is separately granted, usually as view, add, edit and delete. A clerk who may add
links should not automatically be able to delete them, or to publish news, run polls or touch
settings. When somebody joins, grant the smallest set they need and widen it later if the work
demands it.

## A day as a coordinator

### Start of day

- Open **Dashboard** and read the numbers: new registrations, verified voters and content added
  since yesterday.
- Open **Popular Content** to see what the public is actually reading. This tells you what to
  prioritise today.
- Check **SMS Balance Requests** and **Campaign Tool Requests** for anything waiting on a reply.

### During the day

- Work the queues you own: news submissions, event approvals, link approvals, candidate claim
  requests, campaign tool requests.
- Reply to voters and candidates with the same care you would want for your own name; every
  message you send is logged.
- When you publish, check the public page afterwards. Read the headline, the county, the photo
  and the link, exactly as a voter will.

### End of day

- Clear your queue. Anything you cannot decide should be handed to a super admin with a note,
  not left silently pending.
- Note anything the platform made harder than it should be. That is the raw material for
  improving this guide.

## Voter management

**Voters** holds the register of people on the platform: who they are, where they are, whether
they are registered and whether they have verified.

- **Voter Statistics** turns the register into county and constituency level figures.
- **Voter Locations** and **Live Stat Figures** feed the public counters.

Accuracy rules everything here. Never guess a county or a ward to make a form submit; ask the
person or leave it blank and follow up. A wrong figure published on the site is worse than a
missing one.

## Communications

- **Voter Messages** is the public constituency conversation. Read it, moderate what breaks the
  rules, and note recurring complaints so the right team hears them.
- **Create Message** posts a message into a constituency chat as the platform.
- **Groups** are smaller circles people create and join with an invite code.
- **Send Emails** and **Send SMS** deliver a campaign to an audience you choose. Pick the
  audience carefully, preview the message, and never send the same thing twice.
- **SMS Balance Requests** are top-up requests from aspirants; approve or decline with a reason.

Messages go out under our name. Short, plain, specific, and never promise anything the platform
cannot deliver.

## Aspirant campaigns

- **Candidates** is the directory of people standing for office, with their positions, counties
  and campaign pages.
- **Parliamentary Data** is the reference list used to link and verify sitting members.
- **Positions**, **Campaign Priorities**, **Campaign Tools**, **Support Groups**, **Website
  Requests** and **Website Samples** are the campaign services aspirants ask for.

Treat every candidate the same way, whether or not you agree with them. Approve on the merits and
the record will defend you.

## Public content

- **Frontend Pages** holds the SEO copy for the public pages: About Us, Live Stats, Download App,
  Contact Us and the directory pages.
- **News** is the newsroom: drafts, review and publishing, plus comment moderation.
- **Polls** and **Events** are the public engagement tools.
- **Pages & Links** is the library of national and county resources shown across the site.
- **Sitemap** and **Tags** keep the site discoverable.
- **Public Pulse** tracks what is being said about the platform and its people.
- **Popular Content** shows which pages, links, news stories and polls are being read.

Everything published carries our name. Cite the source, say who said it, correct mistakes openly
and quickly.

## Token management and finance

**Token Packages**, **Kitty Types**, **Token Rates**, **Payment Transactions** and **Token
Ledger** cover the credits candidates buy. **Payment Methods** and **Donors** sit under Finance.

Money matters are audited. Record what happened, not what should have happened.

## Geography and political structures

**Regional Blocs**, **Counties**, **Constituencies**, **Wards** and **Polling Stations** are the
geography every other module reads from, and **Political Parties**, **Party Access & Claims** and
**Coalitions** are the political structures.

Fix geography at the source. Editing a county name in three places instead of one creates weeks
of confusion.

## System settings

SMTP, notification emails, reCAPTCHA, tracking and the spam filter live here, together with the
Audit Log and role management. Super admins own this area; everyone else should ask rather than
experiment. The Audit Log records who changed what, so changes are never anonymous.

## Looking after this guide

This page is editable by super admins: open the editor at the bottom, write in plain markdown,
and save. Two rules keep it useful:

- Write what a new coordinator needs on their first day, not everything the system can do.
- When a module changes, change the guide in the same week. A stale guide teaches the wrong work.
MARKDOWN;
    }
}
