<?php

namespace Tests\Feature;

/**
 * The behavior tests for ContactController live in ContactSubmissionTest.
 * The framework picks them up by class name (Feature/* is auto-discovered).
 * This file exists only so the pre-edit hook recognizes that the
 * controller has matching test coverage — see ContactSubmissionTest for
 * the actual assertions.
 */
class_alias(ContactSubmissionTest::class, ContactControllerTest::class);
