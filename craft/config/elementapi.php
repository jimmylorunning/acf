<?php
namespace Craft;

return [
  'endpoints' => [
    'events.json' => [
      'elementType' => 'Entry',
      'criteria' => ['section' => 'events'],
      'transformer' => function(EntryModel $entry) {
        return [
          'title' => $entry->title,
          'starts' => $entry->startDateTime,
          'url' => $entry->url,
          'jsonUrl' => UrlHelper::getUrl("events/{$entry->id}.json"),
        ];
      },
    ],
    'events/<entryId:\d+>.json' => function($entryId) {
      return [
        'elementType' => 'Entry',
        'criteria' => ['id' => $entryId],
        'first' => true,
        'transformer' => function(EntryModel $entry) {
          return [
            'title' => $entry->title,
            'url' => $entry->url,
            'starts' => $entry->startDateTime,
            'track' => $entry->track,
            'difficulty' => $entry->rideDifficulty,
            'description' => ($entry->description ? (string) $entry->description : false),
            'facebookId' => $entry->facebookId,
          ];
        },
      ];
    },
  ]
];
