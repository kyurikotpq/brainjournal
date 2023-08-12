# Brain Journal WordPress Plugin
> Developed as a proof-of-concept without security in mind. NOT meant for production use.

This plugin transforms WordPress sites into a Roam Research-like repository, where each WordPress post becomes a note. 

When saving a post, the plugin runs, checking for links to other posts in the same website. If there is, a record of the link is saved inside the database.

The links are visualized in a D3 force-directed graph that is placed on a page/post via shortcode (remember those? haha. Block-based WordPress was already out, but I didn't have time to look at it!)

There is also support for coloring each post category and using Contact Form 7 as a fleeting notes-capturing tool.

I wrote this during my time at [Nanyang Polytechnic](https://www.linkedin.com/in/kyurikotpq/#:~:text=Development%20Technologist-,Nanyang%20Polytechnic,-%C2%B7%20Contract), so the plugin author is `Nanyang Polytechnic` for now. I was brainstorming a project that could teach REST, Shortcode and Settings APIs as well as database interaction through WordPress.

**Again, this plugin was meant for teaching purposes and not for use in an actual, Internet-facing website.** If you'd like to help with the security aspect, reach out to me on LinkedIn and I'd be happy to discuss!
