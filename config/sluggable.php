<?php

return [

    /**
     * What attributes do we use to build the slug?
     * This can be a single field, like "name" which will build a slug from:
     *
     *     $model->name;
     *
     * Or it can be an array of fields, like ["name", "company"], which builds a slug from:
     *
     *     $model->name . ' ' . $model->company;
     *
     * If you've defined custom getters in your model, you can use those too,
     * since Eloquent will call them when you request a custom attribute.
     *
     * Defaults to null, which uses the toString() method on your model.
     */

    'source' => null,

    /**
     * The maximum length of a generated slug. Defaults to "null", which means
     * no length restrictions are enforced. Set it to a positive integer if you
     * want to make sure your slugs aren't too long.
     */

    'maxLength' => null,

    /**
     * If you are setting a maximum length on your slugs, you may not want the
     * truncated string to split a word in half. The default setting of "true"
     * will ensure this, e.g. with a maxLength of 12:
     *
     *   "my source string" -> "my-source"
     *
     * Setting it to "false" will simply truncate the generated slug at the
     * desired length, e.g.:
     *
     *   "my source string" -> "my-source-st"
     */

    'maxLengthKeepWords' => true,

    /**
     * If left to "null", then use the Laravel Str::slug() function
     * (with the separator defined below).
     *
     * Otherwise, this will be treated as a callable to be used. e.g.:
     *
     *    'method' => [\Illuminate\Support\Str::class, 'slug'],
     */

    'method' => [\Illuminate\Support\Str::class, 'slug'],

    /**
     * Separator to use when generating slugs. Defaults to a hyphen.
     */

    'separator' => '-',

    /**
     * Enforce uniqueness of slugs? Defaults to true.
     * If a generated slug already exists, an incremental numeric
     * value will be appended to the end until a unique slug is found. e.g.:
     *
     *     my-slug
     *     my-slug-1
     *     my-slug-2
     */

    'unique' => true,

    /**
     * If you are enforcing unique slugs, the default is to add an
     * incremental value to the end of the base slug. Alternatively, you
     * can change this value to a static suffix.
     */

    'uniqueSuffix' => null,

    /**
     * What is the first suffix to add to a slug to make it unique?
     * For the default method of adding incremental integers, we start
     * counting at 2, so the list of slugs would be, e.g.:
     *
     *   - my-post
     *   - my-post-2
     *   - my-post-3
     */
    'firstUniqueSuffix' => 2,

    /**
     * Should we include the trashed items when generating a unique slug?
     * This only applies if the softDelete property is set for the Eloquent model.
     * If set to "false", then a new slug could duplicate one that exists on a trashed model.
     * If set to "true", then uniqueness is enforced across trashed and existing models.
     */

    'includeTrashed' => false,

    /**
     * An array of slug names that can never be used for this model,
     * e.g. to prevent collisions with existing routes or controller methods, etc.
     * Defaults to null (i.e. no reserved names).
     */

    'reserved' => ['admin', 'manager', 'root'],

    /**
     * Whether to update the slug value when a model is being
     * re-saved (i.e. already exists). Defaults to false, which
     * means slugs are not updated.
     *
     * Be careful! If you are using slugs to generate URLs, then
     * updating your slug automatically might change your URLs which
     * is probably not a good idea from an SEO point of view.
     * Only set this to true if you understand the possible consequences.
     */

    'onUpdate' => false,

    /**
     * If the default slug engine of Laravel is used, this array of
     * configuration options will be used when instantiating the engine.
     */
    'slugEngineOptions' => [],
];
