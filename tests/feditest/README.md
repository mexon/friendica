# ActivityPub conformance tests taken from Feditest

Feditest is a project to develop conformance tests for ActivityPub implementations.
The tests are purly behavioural, and the same tests are applied across all projects implementing the ActivityPub protocol.

This test suite is intended to replicate and confirm the Feditest results for Friendica, in a structure that is easily accessible to Friendica developers, and which can be run as an automated test.
This should help guard against accidental regressions.

It is not a design goal of Friendica to pass all of the Feditest tests.
Where Friendica's implementation diverges from the spec, these tests will expect and enforce Friendica's choices.
A "pass" in these tests might mean that it fails the equivalent Feditest test in the expected way.
A 100% score in these tests does not mean that Friendica conforms to the ActivityPub spec.
