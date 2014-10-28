<div id="bugDialog" class="dialog">
    <div class="dialogTitle">
        <a class="toggleDialog button titleClose" href="#"></a>
        Report Bug
    </div>
    <div class="dialog_window" id="report_bug">
        <form action="#" id="report_form">
            <div class='bugItem'>
                <div class="left"><label class="item">Summary:</label></div>
                <div class="right">
                    <input id='Summary' type='text' class='text' name='Summary'?>
                </div>
            </div>
            <div class='bugItem'>
                <div class='left'>
                    <label class="item">Name:</label>
                </div>
                <div class='right'>
                    <input id='Name' type='text' class='text' name='Name'?>
                </div>
            </div>
            <div class='bugItem'>
                <div class='left'>
                    <label class="item">Email:</label>
                </div>
                <div class='right'>
                    <input id='Email' type='text' class='text' name='Email'?>
                </div>
            </div>
            <div class='bugItem'>
                <div class='left'>
                    <label class="item">Priority:</label>
                </div>
                <div class='right'>
                    <select id='Priority' name="Priority">
                        <option value="Priority-Low">
                            Low
                        </option>
                        <option value="Priority-Medium">
                            Medium
                        </option>
                        <option value="Priority-High">
                            High
                        </option>
                        <option value="Priority-Critical">
                            Critical
                        </option>
                    </select>
                </div>
            </div>
            <div class='bugItem'>
                <div class='left'>
                    <label class="item">Description:</label>
                </div>
                <div class='right'>
                    <textarea id='Description' type='text' rows=10 class='text description' name='Description'/>
                </div>
            </div>
            <div class="buttonContainer">
                <a class="button" id="Submit Bug" href="#" onclick="$.submitBug()">Submit</a>
                <a class='toggleDialog button close' href='#'>Close</a>
            </div>
        </form>
    </div>
</div>
