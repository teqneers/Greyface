# Using Greyface

For anyone who logs in to check their mail. No technical background needed.

**The short version.** If a mail you are expecting has not arrived, sign in, find the sender in the
list and click **Auto Whitelist**. It comes through on the sender's next attempt, and that sender
is never delayed again.

The rest of this page explains why the delay happens at all, and what everything on the screen
means.

## Why a mail can be late

Your mail server delays mail from senders it has not seen before. The first delivery attempt is
turned away, and a normal mail server simply tries again a few minutes later, at which point the
mail arrives as usual. This is called greylisting, and it removes most spam, because spam senders
rarely bother to try twice.

The trade-off is that a legitimate mail you are waiting for can sit in that waiting period for a
while. Greyface is where you look, and where you let it through.

## Signing in

Your administrator gives you the address and your account. Once you are in, you see the mail
currently being held for **your** addresses. Nobody else's.

If your list is empty, nothing is being held for you right now. If it is *always* empty, even when
you know a mail is late, your administrator may not have registered your addresses yet: see
[Missing an address?](#things-worth-knowing) below.

## Reading the list

Each row is one delayed delivery attempt.

| Column | Meaning |
|---|---|
| Sender | The part of the sender's address before the @ |
| Domain | The part after the @ |
| Source | The network address of the machine that sent it |
| Recipient | Which of your addresses it was sent to |
| First Seen | When the first attempt arrived |

Sender and Domain together make up the sender's mail address: `anna` and `example.com` mean the
mail came from `anna@example.com`.

**Source** usually looks like an incomplete IP address, for example `203.0.113`. That is deliberate,
not a fault. Large senders deliver from whole banks of machines, so your server remembers the
neighbourhood rather than the single machine, and the sender is not delayed again just because
their mail went out through a different server the second time.

An entry disappears on its own once the sender tries again and the mail is accepted, or after a
while if they never do.

## Letting a mail through

Find the sender in the list and choose **Auto Whitelist**.

Two things happen: this sender is accepted from now on without any delay, and the waiting entry
disappears. Use it when you are expecting something and would rather not wait, or when a sender you
deal with regularly keeps getting delayed.

If you change your mind, the toast that appears has an **Undo**.

## Deleting an entry

**Delete** removes the record of the waiting attempt. It does not block the sender and it does not
let them through: if they try again, they simply start over and are delayed once more.

Whitelisting is almost always what you want. Delete is for tidying up entries you know are junk.

## Things worth knowing

**Whitelisting cannot lose mail.** It only removes a delay.

**You cannot see other people's mail.** Only mail addressed to the addresses your administrator has
assigned to you.

**Greyface does not show the message.** It knows a delivery was attempted, who by and to whom. It
never sees the contents.

**Tagged addresses work too.** If you hand out `you+shopping@example.com`, mail to it shows up here
under your ordinary address. Your administrator does not need to register a separate alias for every
tag you invent.

**Missing an address?** If mail to one of your addresses never appears here, ask your administrator
to add that address to your account.

The question mark at the top right repeats this explanation, and describes the columns, whenever you
need it.
