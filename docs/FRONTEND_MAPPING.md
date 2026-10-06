# Frontend feature -> API -> Controller -> Service -> Model -> Table

Filled in Part 2/3 after inspecting the existing Upadhyay JS (localStorage, mock data).

| Frontend feature | API | Controller | Service | Model | Table |
|---|---|---|---|---|---|
| Company search | GET /companies/search | Company\CompanyController | CompanyService | Company | companies, company_profiles |
| Branch request | POST /branches/requests | Branch\BranchRequestController | BranchService | BranchRequest | branch_requests |
| Branch approve | POST /branches/requests/{id}/approve | Branch\BranchRequestController | BranchService + BranchCodeGenerator | CompanyBranch | company_branches |
| Job post | POST /jobs | Job\JobController | JobService | JobPost | job_posts, job_skills |
| Apply | POST /jobs/{job}/apply | Application\ApplicationController | ApplicationService | Application | applications |
