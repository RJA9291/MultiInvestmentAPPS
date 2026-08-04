# Contributing to MultiInvestmentAPPS (AIOS)

This document describes the branching, commit, and pull request conventions used on this project. For coding standards, see docs/10_LARAVEL_STANDARD.md. For the full Git workflow, see docs/11_GITHUB_WORKFLOW.md.

## Branching

Work happens on feature branches created from main, named feature/<short-description>, fix/<short-description>, or chore/<short-description>. main is expected to stay deployable at all times.

## Commits

Commit messages should be short, present-tense, and describe what changed and why. Group related changes into a single commit rather than committing every incremental edit.

## Pull Requests

Open a pull request from your feature branch into main when the work is ready for review. Describe what changed, why, and how it was tested, and link any related issues. Keep pull requests focused and reasonably small so review stays practical.

## Code Review

At least one review is expected before merging into main. Reviewers should check for correctness, adherence to the coding standards in docs/10_LARAVEL_STANDARD.md, and any security or data-handling concerns relevant to an investment platform.

## Documentation

If a change affects product behavior, architecture, or business rules, update the relevant file under docs/ in the same pull request.
