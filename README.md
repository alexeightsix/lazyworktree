## LazyWorktrees

###  ⚠️ STILL VERY MUCH WORK IN PROGRESS ⚠️ ###

#### What is it?
A simple TUI to manage GIT Worktrees. It was written in PHP using the [Laravel Prompts Library](https://github.com/laravel/prompts).

### Prerequisites
- GIT
- PHP >= 8.2
- Composer
- Linux (Not tested on other platforms)


#### Installation
``` bash
git clone git@github.com:alexeightsix/lazyworktree.git ~/.local/share/
cd ~/.local/share/lazyworktree
composer install
sudo ln -s ~/.local/share/lazyworktree/lazyworktree /usr/local/bin/lazyworktree
```

#### Alias
``` bash
export lw='/usr/local/bin/lazyworktree'
```

#### Usage
``` bash
mkdir /my_repo 
cd /my_repo
lazyworktree init
```

### Initalize TUI
``` bash
lazyworktree 
```

### Shortcuts
``` bash
lazyworktree create
lazyworktree delete
lazyworktree list
lazyworktree switch
```

### IDE Integration
If you're creating a plugin for your IDE you can use the following commands to interact with the TUI
``` bash
lazyworktree api list
lazyworktree api switch
```

### Hooks 
Execute a shell script before or after an action is performed. The file must exist in either the root of your project or worktree root.
```
hook_before_switch.sh
hook_after_switch.sh

hook_before_create.sh
hook_after_create.sh

hook_before_delete.sh
hook_after_delete.sh
```

### Neovim Integration with Telescope
```lua 
vim.keymap.set('n', '<leader>wt', function()
  require("plugins.lwt").switch()
end)
```
```lua
local M = {}

M.is_worktree = function()
  return vim.fn.filereadable(vim.fn.getcwd() .. "/../../lazywt.json")
end

M.exec = function(cmd)
  local f = assert(io.popen(cmd, 'r'))
  local s = assert(f:read('*a'))
  f:close()
  s = string.gsub(s, '^%s+', '')
  s = string.gsub(s, '%s+$', '')
  s = string.gsub(s, '[\n\r]+', ' ')
  return s
end

M.checkhealth = function()
  local is_worktree = M.is_worktree()
  local has_binary = os.execute("which lazyworktree") == 0
  return is_worktree and has_binary
end

M.list_worktrees = function()
  local code = M.exec("cd " .. vim.fn.getcwd() .. "/../../" .. " && lazyworktree api list")
  local json = vim.fn.json_decode(code)
  local worktrees = {}

  for _, v in pairs(json) do
    table.insert(worktrees, v.branch)
  end

  return worktrees;
end

M.switch = function()
  if not M.checkhealth() then
    vim.notify("Lazy Worktree is not installed or not in a worktree")
    return nil
  end

  local action_state = require "telescope.actions.state"
  local actions = require "telescope.actions"
  local conf = require("telescope.config").values
  local dropdown = require("telescope.themes").get_dropdown {}
  local finders = require "telescope.finders"
  local pickers = require "telescope.pickers"
  local _, res = pcall(M.list_worktrees)

  pickers.new(dropdown, {
    prompt_title = "Switch to Worktree",
    finder = finders.new_table {
      results = res or {},
    },
    sorter = conf.generic_sorter(),
    attach_mappings = function(prompt_bufnr)
      actions.select_default:replace(function()
        actions.close(prompt_bufnr)
        local selection = action_state.get_selected_entry()
        local branch = selection.value
        M.exec("cd " .. vim.fn.getcwd() .. "/../../" .. " && lazyworktree api switch" .. branch)
        vim.api.nvim_set_current_dir(vim.fn.getcwd() .. "/../" .. branch)
      end)
      return true
    end,
  }):find()
end

return M;
```
